<?php

class Application
{
    protected $controller = 'homeController';
    protected $model = false;
    protected $method = 'index';
    protected $params = [];

    public function __construct()
    {
        session_start();

        require_once '../application/lang/lang.php';
        require_once '../application/etc/functions.php';
        require_once '../application/plugins/PHPImageWorkshop/ImageWorkshop.php';

        $url = $this->parseUrl();
        $model = ucfirst($url[0]).'Model';

        if(file_exists('../application/models/'.$model.'.php')){
            $this->model = $model;
            require_once '../application/models/'.$this->model.'.php';
        }

        $controller = lcfirst($url[0]).'Controller';

        if(file_exists('../application/controllers/'.$controller.'.php')){
            $this->controller = $controller;
            unset($url[0]);
        }

        require_once '../application/controllers/'.$this->controller.'.php';
        $this->controller = new $this->controller;

        if (get_magic_quotes_gpc()) {
            $process = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);
            while (list($key, $val) = each($process)) {
                foreach ($val as $k => $v) {
                    unset($process[$key][$k]);
                    if (is_array($v)) {
                        $process[$key][stripslashes($k)] = $v;
                        $process[] = &$process[$key][stripslashes($k)];
                    } else {
                        $process[$key][stripslashes($k)] = stripslashes($v);
                    }
                }
            }
            unset($process);
        }

        $this->controller->request = array_merge($_GET, $_POST);

        if (count($this->controller->request) != 0) {

            foreach (array_keys($this->controller->request) as $key) {
                $this->controller->request[$key] = $this->xss_clean($this->controller->request[$key]);
                $this->controller->request[$key] = $this->escape($this->controller->request[$key]);
            }
        }

        $this->controller->file = $_FILES;
        $this->controller->cookie = $_COOKIE;

        if (count($this->controller->cookie) != 0) {

            foreach (array_keys($this->controller->cookie) as $key) {
                $this->controller->cookie[$key] = $this->xss_clean($this->controller->cookie[$key]);
                $this->controller->cookie[$key] = $this->escape($this->controller->cookie[$key]);
            }
        }

        if (isset($url[1])) {

            if (method_exists($this->controller, $url[1])) {
                $this->method = $url[1];
                unset($url[1]);
            } else {
                $this->method = 'error404';
            }
        }

        $this->controller != 'user' && $this->method != 'login' ? $this->controller->authUser() : null;
        $this->params = $url ? array_values($url) : [];
        call_user_func_array([$this->controller, $this->method], $this->params);
    }

    public function parseUrl()
    {
        if(isset($_GET['url'])){
            $url = explode('/', filter_var(rtrim($_GET['url'], '/'), FILTER_SANITIZE_URL));;
            return $url;
        }
    }

    public function escape($string)
    {
        if (is_string($string)) {
            $string = trim($string);
            $string = stripslashes($string);
            return $string;
        } else {
            return $string;
        }
    }

    public function xss_clean($data)
    {
        $data = str_replace(array('&amp;','&lt;','&gt;'), array('&amp;amp;','&amp;lt;','&amp;gt;'), $data);
        $data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
        $data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);

        if(is_object($data) || is_array($data)){
            foreach($data as &$value)
                $value = html_entity_decode($value, ENT_COMPAT, 'UTF-8');
        }else $data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');

        $data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $data);
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $data);
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $data);
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $data);
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $data);
        $data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);

        do{
            $old_data = $data;
            $data = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $data);
        }while($old_data !== $data);

        return $data;
    }

    public static function getConfig()
    {
        $localFile = '../application/core/Config.local.php';
        $remoteFile = '../application/core/Config.php';
        file_exists($localFile) ? require_once $localFile : require_once $remoteFile;
        return config();
    }
}