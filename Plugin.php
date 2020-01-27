<?php
/**
 * 附件上传Github仓库插件
 *
 * @package UploadGithubForTypecho
 * @author AyagawaSeirin
 * @link https://qwq.best/
 * @version 1.1.0
 * @dependence 1.0-*
 *
 */

class UploadGithubForTypecho_Plugin implements Typecho_Plugin_Interface
{
    //上传文件目录
    const UPLOAD_DIR = '/usr/uploads';


    /**
     * 插件激活接口
     */
    public static function activate()
    {
        Typecho_Plugin::factory('Widget_Upload')->uploadHandle = array('UploadGithubForTypecho_Plugin', 'uploadHandle');
        Typecho_Plugin::factory('Widget_Upload')->modifyHandle = array('UploadGithubForTypecho_Plugin', 'modifyHandle');
        Typecho_Plugin::factory('Widget_Upload')->deleteHandle = array('UploadGithubForTypecho_Plugin', 'deleteHandle');
        Typecho_Plugin::factory('Widget_Upload')->attachmentHandle = array('UploadGithubForTypecho_Plugin', 'attachmentHandle');
        Typecho_Plugin::factory('Widget_Upload')->attachmentDataHandle = array('UploadGithubForTypecho_Plugin', 'attachmentDataHandle');
        return _t('插件已激活，请前往设置');
    }

    /**
     * 个人用户的配置面板
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form)
    {
    }

    /**
     * 插件禁用接口
     */
    public static function deactivate()
    {
        return _t('插件已禁用');
    }


    /**
     * 插件配置面板
     */
    public static function config(Typecho_Widget_Helper_Form $form)
    {
        echo '
                <style>
                p.notice {
                    line-height: 1.75;
                    padding: .5rem;
                    padding-left: .75rem;
                    border-left: solid 4px #fbbc05;
                    background: rgba(0,0,25,.025);
                }
                .notice {
                    background: #FFF6BF;
                    color: #8A6D3B;
                }
                </style>
                <script src="https://cdn.jsdelivr.net/gh/jquery/jquery/dist/jquery.min.js"></script>
                <p id="UploadGithubForTypecho-check-update" class="notice">正在检查插件更新...</p>
                <script>
                    window.onload = function()
                    {
                        document.getElementsByName("desc1")[0].type = "hidden";
                        document.getElementsByName("desc2")[0].type = "hidden";
                        document.getElementsByName("desc3")[0].type = "hidden";
                        var notice = "正在检查更新...";
                        $.ajax({
                            url: "https://api.github.com/repos/AyagawaSeirin/UploadGithubForTypecho/releases",
                            async: true,
                            type: "GET",
                            success: function (data) {
                                var now = "1.1.0";
                                var newest = data[0][\'tag_name\'];
                                if(newest == null){
                                    notice = "检查更新失败，请手动访问插件项目地址获取更新。";
                                }else if(newest == now){
                                    notice = "您当前的插件是最新版本：v" + newest;
                                } else {
                                    notice = "插件需要更新，当前版本：v" + now + "，最新版本：v" + newest + "。<a href=\'https://github.com/AyagawaSeirin/UploadGithubForTypecho\'>点击这里</a>获取最新版本。";
                                }
                                $(\'#UploadGithubForTypecho-check-update\').html(notice);
                            },
                            error: function (jqXHR, textStatus, errorThrown) {
                                notice = "检查更新失败，请手动访问插件项目地址获取更新。";
                                $(\'#UploadGithubForTypecho-check-update\').html(notice);
                            }
                        });
                        
                    }
                </script>
        
       
        ';
        $desc1 = new Typecho_Widget_Helper_Form_Element_Text('desc1', NULL, '', _t('插件使用说明：'), _t("
        <ol>
        <li>本插件用于将文章附件(如图片)上传至您的(公开的)Github的仓库中，并使用jsDelivr访问仓库文件达到优化文件访问速度的目的。了解jsDelivr应用于博客中的优势，您可以<a href='https://qwq.best/dev/113.html' target='_blank'>点击这里</a>。<br></li>
        <li>项目地址：<a href='https://github.com/AyagawaSeirin/UploadGithubForTypecho' target='_blank'>https://github.com/AyagawaSeirin/UploadGithubForTypecho</a><br></li>
        <li>插件使用说明与教程：<a href='https://qwq.best/dev/152.html' target='_blank'>https://qwq.best/dev/152.html</a><br></li>
        <li>插件不会验证配置的正确性，请自行确认配置信息正确，否则不能正常使用。<br></li>
        <li>插件会替换所有之前上传的文件的链接，若启用插件前存在已上传的文件，请自行将其上传至仓库相同目录中以保证正常显示；同时，禁用插件也会导致链接恢复。上传的文件保存在本地的问题请看下面相关配置项。</li>
        <li>注意：由于CDN缓存问题，修改文件后访问链接可能仍然是旧文件，所以建议删掉旧文件再上传新文件，不建议使用修改文件功能。jsDelivr刷新缓存功能暂未推出，推出后本插件会及时更新。</li>
        <li>Github API限制每个IP每小时只能请求60次接口，请控制您操作图片(上传修改删除)的频率。</li>
        </ol>
        "));
        $github_user = new Typecho_Widget_Helper_Form_Element_Text('githubUser',
            NULL, '', _t('Github用户名'), _t('您的Github用户名'));
        $github_repo = new Typecho_Widget_Helper_Form_Element_Text('githubRepo',
            NULL, '', _t('Github仓库名'), _t('您的Github仓库名'));
        $github_token = new Typecho_Widget_Helper_Form_Element_Text('githubToken', NULL, '', _t('Github账号token'), _t('不知道如何获取账号token请<a href="https://qwq.best/dev/151.html" target="_blank">点击这里</a>'));
        $github_directory = new Typecho_Widget_Helper_Form_Element_Text('githubDirectory',
            NULL, '/usr/uploads', _t('Github仓库内的上传目录'), _t('比如/usr/uploads，最后一位不需要斜杠'));
        $url_type = new Typecho_Widget_Helper_Form_Element_Select('urlType', array('latest' => '访问最新版本', 'direct' => '直接访问'), 'latest', _t('文件链接访问方式：'), _t('建议选择"访问最新版本"。若修改图片，直接访问方式不方便更新缓存。'));
        $desc3 = new Typecho_Widget_Helper_Form_Element_Text('desc3', NULL, '', _t('由于Linux权限问题，可能会由于无法创建目录导致文件保存到本地失败而报错异常，请给予本地上传目录777权限。<br>您也可以选择不保存到本地，但可能导致您的主题或其他插件的某些功能异常。<br>您也可以在每一月手动创建当月的目录，避免出现目录创建失败问题（推荐）。'));
        $if_save = new Typecho_Widget_Helper_Form_Element_Select('ifSave', array('save' => '保存到本地', 'notsave' => '不保存到本地'), 'save', _t('是否保存在本地：'), _t('是否将上传的文件保存在本地。'));
        $desc2 = new Typecho_Widget_Helper_Form_Element_Text('desc2', NULL, '', _t('以下两个参数为选填，留空则为仓库所有者信息。若填写则必须两个都填写。如果您不知道该如何填写，默认即可，不需要修改。'));
        $commit_name = new Typecho_Widget_Helper_Form_Element_Text('commitName', NULL, 'UploadGithubForTypecho', _t('提交文件者名称'), _t('提交Commit的提交者名称，留空则为仓库所属者。'));
        $commit_email = new Typecho_Widget_Helper_Form_Element_Text('commitEmail', NULL, 'UploadGithubForTypecho@typecho.com', _t('提交文件者邮箱'), _t('提交Commit的提交者邮箱，留空则为仓库所属者。'));
        $form->addInput($desc1);
        $form->addInput($github_user->addRule('required', _t('请输入Github用户名')));
        $form->addInput($github_repo->addRule('required', _t('请输入Github仓库名')));
        $form->addInput($github_token->addRule('required', _t('请输入Github账号token')));
        $form->addInput($github_directory->addRule('required', _t('请输入Github上传目录')));
        $form->addInput($url_type);
        $form->addInput($desc3);
        $form->addInput($if_save);
        $form->addInput($desc2);
        $form->addInput($commit_name);
        $form->addInput($commit_email);
    }

    /**
     * 上传文件处理函数
     */
    public static function uploadHandle($file)
    {
        if (empty($file['name'])) {
            return false;
        }
        //获取扩展名
        $ext = self::getSafeName($file['name']);
        //判定是否是允许的文件类型
        if (!Widget_Upload::checkFileType($ext) || Typecho_Common::isAppEngine()) {
            return false;
        }
        //获取设置参数
        $options = Typecho_Widget::widget('Widget_Options')->plugin('UploadGithubForTypecho');
        //获取文件名
        $date = new Typecho_Date($options->gmtTime);
        $fileDir_relatively = self::getUploadDir(true) . '/' . $date->year . '/' . $date->month;
        $fileDir = self::getUploadDir(false) . '/' . $date->year . '/' . $date->month;
        $fileName = sprintf('%u', crc32(uniqid())) . '.' . $ext;
        $path_relatively = $fileDir_relatively . '/' . $fileName;
        $path = $fileDir . '/' . $fileName;
        //获得上传文件
        $uploadfile = self::getUploadFile($file);
        //如果没有临时文件，则退出
        if (!isset($uploadfile)) {
            return false;
        }
        $fileContent = file_get_contents($uploadfile);
        //echo "fileDir:$fileDir;fileDir_relatively:$fileDir_relatively;path:$path;path_relatively:$path_relatively;";


        /* 上传到Github */
        $data = array(
            "message" => "Upload file " . $fileName,
            "content" => base64_encode($fileContent),
        );
        if ($options->commitName != null && $options->commitEmail != null) {
            $committer = array(
                "name" => $options->commitName,
                "email" => $options->commitEmail
            );
            $data['committer'] = $committer;
        }
        $header = array(
            "Content-Type:application/json",
            "User-Agent:" . $options->githubRepo
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.github.com/repos/" . $options->githubUser . "/" . $options->githubRepo . "/contents" . $path_relatively . "?access_token=" . $options->githubToken);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        $output = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code != 201) {
            $output = json_decode($output,true);
            self::writeErrorLog($path_relatively, "[Github][upload][" . $http_code . "]" . $output['message']);
        }

        /* 写到本地文件 */
        if ($options->ifSave == 'save') {
            if (!is_dir($fileDir)) {
                if (self::makeUploadDir($fileDir)) {
                    file_put_contents($path, $fileContent);
                } else {
                    //文件写入失败，写入错误日志
                    self::writeErrorLog($path_relatively, "[local]Directory creation failed");
                }
            } else {
                file_put_contents($path, $fileContent);
            }
        }

        //返回相对存储路径
        return array(
            'name' => $file['name'],
            'path' => $path_relatively,
            'size' => $file['size'],
            'type' => $ext,
            'mime' => @Typecho_Common::mimeContentType($path)
        );
    }

    /**
     * 修改文件处理函数
     */
    public static function modifyHandle($content, $file)
    {
        if (empty($file['name'])) {
            return false;
        }

        //获取扩展名
        $ext = self::getSafeName($file['name']);
        //判定是否是允许的文件类型
        if ($content['attachment']->type != $ext || Typecho_Common::isAppEngine()) {
            return false;
        }
        //获取设置参数
        $options = Typecho_Widget::widget('Widget_Options')->plugin('UploadGithubForTypecho');
        //获取文件路径
        $path = $content['attachment']->path;
        //获得上传文件
        $uploadfile = self::getUploadFile($file);
        //如果没有临时文件，则退出
        if (!isset($uploadfile)) {
            return false;
        }
        $fileContent = file_get_contents($uploadfile);

        //判断仓库内相对路径
        $filename = __TYPECHO_ROOT_DIR__ . $path;//本地文件绝对路径
        $github_path = $options->githubDirectory . str_replace(self::getUploadDir(), "", $content['attachment']->path);

        //获取文件sha
        $header = array(
            "Content-Type:application/json",
            "User-Agent:" . $options->githubRepo
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.github.com/repos/" . $options->githubUser . "/" . $options->githubRepo . "/contents" . $github_path);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $output = json_decode(curl_exec($ch), true);
        curl_close($ch);
        $sha = $output['sha'];

        /* 更新Github仓库内文件 */
        $data = array(
            "message" => "Modify file " . str_replace(self::getUploadDir(), "", $content['attachment']->path),
            "content" => base64_encode($fileContent),
            "sha" => $sha,
        );
        if ($options->commitName != null && $options->commitEmail != null) {
            $committer = array(
                "name" => $options->commitName,
                "email" => $options->commitEmail
            );
            $data['committer'] = $committer;
        }
        $header = array(
            "Content-Type:application/json",
            "User-Agent:" . $options->githubRepo
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.github.com/repos/" . $options->githubUser . "/" . $options->githubRepo . "/contents" . $path . "?access_token=" . $options->githubToken);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        $output = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code != 200) {
            $output = json_decode($output,true);
            self::writeErrorLog($github_path, "[Github][modify][" . $http_code . "]" . $output['message']);
        }

        //开始处理本地的文件
        if ($options->ifSave == 'save') {
            if (file_exists($filename)) {
                unlink($filename);
            }
            file_put_contents($filename, $fileContent);
        }

        if (!isset($file['size'])) {
            $file['size'] = filesize($path);
        }

        //返回相对存储路径
        return array(
            'name' => $content['attachment']->name,
            'path' => $content['attachment']->path,
            'size' => $file['size'],
            'type' => $content['attachment']->type,
            'mime' => $content['attachment']->mime
        );
    }

    /**
     * 删除文件处理函数
     */
    public static function deleteHandle(array $content)
    {
        //获取设置参数
        $options = Typecho_Widget::widget('Widget_Options')->plugin('UploadGithubForTypecho');

        //判断仓库内相对路径
        $filename = __TYPECHO_ROOT_DIR__ . $content['attachment']->path;
        $github_path = $options->githubDirectory . str_replace(self::getUploadDir(), "", $content['attachment']->path);

        //获取文件sha
        $header = array(
            "Content-Type:application/json",
            "User-Agent:" . $options->githubRepo
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.github.com/repos/" . $options->githubUser . "/" . $options->githubRepo . "/contents" . $github_path);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $output = json_decode(curl_exec($ch), true);
        curl_close($ch);
        $sha = $output['sha'];

        /* 删除Github仓库内文件 */
        $data = array(
            "message" => "Delete file",
            "sha" => $sha,
        );
        if ($options->commitName != null && $options->commitEmail != null) {
            $committer = array(
                "name" => $options->commitName,
                "email" => $options->commitEmail
            );
            $data['committer'] = $committer;
        }
        $header = array(
            "Content-Type:application/json",
            "User-Agent:" . $options->githubRepo
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.github.com/repos/" . $options->githubUser . "/" . $options->githubRepo . "/contents" . $github_path . "?access_token=" . $options->githubToken);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        $output = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code != 200) {
            $output = json_decode($output,true);
            self::writeErrorLog($github_path, "[Github][delete][" . $http_code . "]" . $output['message']);
        }
        //删除本地文件
        if ($options->ifSave == 'save' && file_exists($filename) == true) {
            unlink($filename);
        }

        return true;
    }

    /**
     * 获取实际文件数据
     */
    public static function attachmentDataHandle($content)
    {
        $options = Typecho_Widget::widget('Widget_Options')->plugin('UploadGithubForTypecho');
        $filePath = "https://cdn.jsdelivr.net/gh/" . $options->githubUser . "/" . $options->githubRepo . "@latest" . $content['attachment']->path;
        return file_get_contents($filePath);
    }


    /**
     * 获取实际文件绝对访问路径
     */
    public static function attachmentHandle($content)
    {
        //获取设置参数
        $options = Typecho_Widget::widget('Widget_Options')->plugin('UploadGithubForTypecho');
        $latest = "";
        if ($options->urlType == "latest") {
            $latest = "@latest";
        }
        return Typecho_Common::url($content['attachment']->path, "https://cdn.jsdelivr.net/gh/" . $options->githubUser . "/" . $options->githubRepo . $latest);
    }

    private static function writeErrorLog($path, $content)
    {
        $date = date('[Y/m/d H:i:s]', time());
        $text = $date . " " . $path . " " . $content . "\n";
        $log_file = dirname(__FILE__) . "/log/error.log";
        if (!file_exists($log_file)) {
            $file = fopen($log_file, 'w');
        } else {
            $file = fopen($log_file, 'ab+');
        }
        fwrite($file, $text);
    }

    /**
     * 获取安全的文件名
     */
    private static function getSafeName($name)
    {
        $name = str_replace(array('"', '<', '>'), '', $name);
        $name = str_replace('\\', '/', $name);
        $name = false === strpos($name, '/') ? ('a' . $name) : str_replace('/', '/a', $name);
        $info = pathinfo($name);
        $name = substr($info['basename'], 1);
        return isset($info['extension']) ? strtolower($info['extension']) : '';
    }

    /**
     * 获取文件上传目录
     */
    private static function getUploadDir($relatively = true)
    {
        if ($relatively) {
            if (defined('__TYPECHO_UPLOAD_DIR__')) {
                return __TYPECHO_UPLOAD_DIR__;
            } else {
                return self::UPLOAD_DIR;
            }
        } else {
            return Typecho_Common::url(defined('__TYPECHO_UPLOAD_DIR__') ? __TYPECHO_UPLOAD_DIR__ : self::UPLOAD_DIR,
                defined('__TYPECHO_UPLOAD_ROOT_DIR__') ? __TYPECHO_UPLOAD_ROOT_DIR__ : __TYPECHO_ROOT_DIR__);
        }
    }

    /**
     * 获取上传文件
     */
    private static function getUploadFile($file)
    {
        return isset($file['tmp_name']) ? $file['tmp_name'] : (isset($file['bytes']) ? $file['bytes'] : (isset($file['bits']) ? $file['bits'] : ''));
    }

    /**
     * 创建上传路径
     */
    private static function makeUploadDir($path)
    {
        $path = preg_replace("/\\\+/", '/', $path);
        $current = rtrim($path, '/');
        $last = $current;

        while (!is_dir($current) && false !== strpos($path, '/')) {
            $last = $current;
            $current = dirname($current);
        }

        if ($last == $current) {
            return true;
        }

        if (!@mkdir($last)) {
            return false;
        }

        $stat = @stat($last);
        $perms = $stat['mode'] & 0007777;
        @chmod($last, $perms);

        return self::makeUploadDir($path);
    }
}