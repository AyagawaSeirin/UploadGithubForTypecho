<?php
/**
 * 附件上传Github仓库插件
 *
 * @package UploadGithubForTypecho
 * @author PPLin
 * @link https://qwq.best/
 * @version 1.0.0
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
                        $.ajax({
                            url: "https://api.github.com/repos/AyagawaSeirin/UploadGithubForTypecho/releases",
                            async: true,
                            type: "GET",
                            success: function (data) {
                                
                            }
                        });
                    }
                </script>
        
       
        ';
        $desc1 = new Typecho_Widget_Helper_Form_Element_Text('desc1', NULL, '', _t('插件使用说明：'),_t("
        <ol>
        <li>本插件用于将文章附件(如图片)上传至您的(公开的)Github的仓库中，并使用jsDelivr访问仓库文件达到优化文件访问速度的目的。了解jsDelivr应用于博客中的优势，您可以<a href='https://qwq.best/dev/113.html' target='_blank'>点击这里</a>。<br></li>
        <li>项目地址：<a href='https://github.com/AyagawaSeirin/' target='_blank'>https://github.com/AyagawaSeirin/</a><br></li>
        <li>当前版本：v1.0.0，您可以在上面的Github项目地址检查更新。<br></li>
        <li>插件不会验证配置的正确性，请自行确认配置信息正确，否则不能正常使用。<br></li>
        <li>插件会替换所有之前上传的文件的链接，若启用插件前存在已上传的文件，请自行将其上传至仓库相同目录中以保证正常显示；同时，禁用插件也会导致链接恢复。插件在上传文件同时也会在本地相应位置留下文件，所以禁用插件后文件链接恢复不用担心文件不存在问题。</li>
        </ol>
        "));
        $github_user = new Typecho_Widget_Helper_Form_Element_Text('githubUser',
            NULL, 'AyagawaSeirin', _t('Github用户名'), _t('您的Github用户名'));
        $github_repo = new Typecho_Widget_Helper_Form_Element_Text('githubRepo',
            NULL, 'Picture', _t('Github仓库名'), _t('您的Github仓库名'));
        $github_token = new Typecho_Widget_Helper_Form_Element_Text('githubToken', NULL, '24f3b95da0abe0215afeb32dd2ab9703594501d8', _t('Github账号token'), _t('不知道如何获取账号token请<a href="#" target="_blank">点击这里</a>'));
        $github_directory = new Typecho_Widget_Helper_Form_Element_Text('githubDirectory',
            NULL, '/usr/uploads', _t('Github仓库内的上传目录'), _t('比如/usr/uploads，最后一位不需要斜杠'));
        $url_type = new Typecho_Widget_Helper_Form_Element_Select('urlType',
            array('latest' => '访问最新版本', 'direct' => '直接访问'), 'latest', _t('文件链接访问方式：'), _t('建议选择"访问最新版本"。若修改图片，直接访问方式不方便更新缓存'));
        $desc2 = new Typecho_Widget_Helper_Form_Element_Text('desc2', NULL, '', _t('以下两个参数为选填，留空则为仓库所有者信息。若填写则必须两个都填写。如果您不知道该如何填写，默认即可，不需要修改。'));
        $commit_name = new Typecho_Widget_Helper_Form_Element_Text('commitName',
            NULL, 'UploadGithubForTypecho', _t('提交文件者名称'), _t('提交Commit的提交者名称，留空则为仓库所属者。'));
        $commit_email = new Typecho_Widget_Helper_Form_Element_Text('commitEmail',
            NULL, 'i@qwq.best', _t('提交文件者邮箱'), _t('提交Commit的提交者名称，留空则为仓库所属者。'));
        $form->addInput($desc1);
        $form->addInput($github_user->addRule('required', _t('请输入Github用户名')));
        $form->addInput($github_repo->addRule('required', _t('请输入Github仓库名')));
        $form->addInput($github_token->addRule('required', _t('请输入Github账号token')));
        $form->addInput($github_directory->addRule('required', _t('请输入Github上传目录')));
        $form->addInput($url_type);
        $form->addInput($desc2);
        $form->addInput($commit_name);
        $form->addInput($commit_email);

        echo '<script>
        </script>';
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
        $fileDir = self::getUploadDir() . '/' . $date->year . '/' . $date->month;
        $fileName = sprintf('%u', crc32(uniqid())) . '.' . $ext;
        $path = $fileDir . '/' . $fileName;
        //获得上传文件
        $uploadfile = self::getUploadFile($file);
        //如果没有临时文件，则退出
        if (!isset($uploadfile)) {
            return false;
        }
        $fileContent = file_get_contents($uploadfile);

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
        curl_setopt($ch, CURLOPT_URL, "https://api.github.com/repos/" . $options->githubUser . "/" . $options->githubRepo . "/contents" . $path . "?access_token=" . $options->githubToken);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        $output = curl_exec($ch);
        curl_close($ch);

        /* 写到本地文件 */
        file_put_contents(__TYPECHO_ROOT_DIR__ . $path, $fileContent);


        //返回相对存储路径
        return array(
            'name' => $file['name'],
            'path' => $path,
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
        $filename = __TYPECHO_ROOT_DIR__. $content['attachment']->path;//本地文件绝对路径
        $github_path = $options->githubDirectory . str_replace(self::getUploadDir(),"",$content['attachment']->path);

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
        $output = json_decode(curl_exec($ch),true);
        curl_close($ch);
        $sha = $output['sha'];

        /* 更新Github仓库内文件 */
        $data = array(
            "message" => "Update file " . str_replace(self::getUploadDir(),"",$content['attachment']->path),
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
        curl_close($ch);
//        print_r($output);

        //开始处理本地的文件
        unlink($filename);
        file_put_contents($filename, $fileContent);

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
        $filename = __TYPECHO_ROOT_DIR__. $content['attachment']->path;
        $github_path = $options->githubDirectory . str_replace(self::getUploadDir(),"",$content['attachment']->path);

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
        $output = json_decode(curl_exec($ch),true);
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
        curl_close($ch);

        //删除本地文件
        unlink($filename);

        return true;
    }

    /**
     * 获取实际文件数据
     */
    public static function attachmentDataHandle($content)
    {
        $options = Typecho_Widget::widget('Widget_Options')->plugin('UploadGithubForTypecho');
        $filePath = "https://cdn.jsdelivr.net/gh/" . $options->githubUser . "/" . $options->githubRepo . "@latest" . $content['attachment']->path;
        return file_get_contents($filePath);//getUploadDir()
    }


    /**
     * 获取实际文件绝对访问路径
     */
    public static function attachmentHandle($content)
    {
        //获取设置参数
        $options = Typecho_Widget::widget('Widget_Options')->plugin('UploadGithubForTypecho');
        return Typecho_Common::url($content['attachment']->path, "https://cdn.jsdelivr.net/gh/" . $options->githubUser . "/" . $options->githubRepo . "@latest");
    }

    /**
     * 获取安全的文件名
     */
    private static function getSafeName(&$name)
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
    private static function getUploadDir()
    {
        if (defined('__TYPECHO_UPLOAD_DIR__')) {
            return __TYPECHO_UPLOAD_DIR__;
        } else {
            return self::UPLOAD_DIR;
        }
    }

    /**
     * 获取上传文件
     */
    private static function getUploadFile($file)
    {
        return isset($file['tmp_name']) ? $file['tmp_name'] : (isset($file['bytes']) ? $file['bytes'] : (isset($file['bits']) ? $file['bits'] : ''));
    }
}