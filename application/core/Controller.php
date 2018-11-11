<?php

class Controller
{
    public $config;
    public $request;
    public $file;
    public $cookie;
    public $user;
    protected $model;

    public function __construct()
    {
        $this->config = Application::getConfig();

        if ($this->config['maintenance']) {
            $this->view('maintenance');
            exit();
        }

        $this->model('User');
    }

    public function authUser()
    {
        if (isset($this->cookie['userId']) && isset($_SESSION['me'])) {
            $this->user = false;
            $userId = false;
            isset($this->cookie['userId']) ? $userId = $this->cookie['userId'] : null;
            isset($_SESSION['me']) ? $userId = $_SESSION['me'] : null;
            $user = new User($userId);

            if ($user) {
                $logOut =
                    $_SESSION['me'] != $this->cookie['userId']
                    || $this->cookie['session'] != $user->session
                    || getIP() != $user->ip_address;

                if ($logOut) {
                    $_SESSION = array();
                    $this->user = false;
                    header('Location: '.$this->config['url']);
                } else {
                    $this->user = $user;
                    $this->user->setSessionCookie();
                    $this->user->updateLastAction();
                    return true;
                }
            }
        }

        return false;
    }

    public function model($model)
    {
        $model = ucfirst($model).'Model';
        $path = '../application/models/'.$model.'.php';
        file_exists($path) ? require_once $path : false;
    }

    public function view($view, $data = [])
    {
        if (!isset($_SESSION['loggedIn'])) {
            $this->initFacebook('FacebookRedirectLoginHelper');
            $fbLoginHelper = new \Facebook\FacebookRedirectLoginHelper($this->config['url'] . '/user/login');
            $data['fbLoginUrl'] = $fbLoginHelper->getLoginUrl(array('scope' => 'email'));
        }

        if (isset($this->request['alert'])) {
            $alertText = output($this->request['alert']);
            isset($this->request['alertType']) ?
                $data['alert'] = array('type' => $this->request['alertType'], 'text' => $alertText) :
                $data['alert'] = array('type' => 'info', 'text' => $alertText);
        }

        !empty($this->user) ?
            $data['user'] = $this->user :
            $data['user'] = false;
        !empty($this->user) ?
            $data['wallet'] = $data['user']->getWallet() :
            $data['wallet'] = false;
        $data['config'] = Application::getConfig();
        require_once '../application/core/View.php';
        require_once '../application/views/htmlHead.php';
        $path = '../application/views/'.$view.'.php';
        !file_exists($path) ? $path = '../application/views/error404.php' : null;
        View::render($path, $data);
        exit;
    }

    public function error404()
    {
        $this->view('error404');
    }

    public function alert(array $alert, $view = null)
    {
        is_string($view) ? null : $view = '';
        isset($alert['type']) ? null : $alert['type'] = 'info';
        header('Location: '.Application::getConfig()['url'].'/'.$view.'?alert='.$alert['text'].'&alertType='.$alert['type']);
    }

    public function getSecurimage()
    {
        require_once '../public/plugins/securimage/securimage.php';
        return new Securimage();
    }

    public function getBitcoind()
    {
        $localFile = '../application/core/Bitcoind.local.php';
        $remoteFile = '../application/core/Bitcoind.php';
        file_exists($localFile) ? require_once $localFile : require_once $remoteFile;
        return bitcoind();
    }

    public function initFacebook($path = [])
    {
        require_once '../application/core/Facebook.php';
        require_once '../application/plugins/facebook/GraphObject.php';
        require_once '../application/plugins/facebook/GraphUser.php';
        require_once '../application/plugins/facebook/FacebookResponse.php';
        require_once '../application/plugins/facebook/FacebookSDKException.php';
        require_once '../application/plugins/facebook/FacebookRequestException.php';
        require_once '../application/plugins/facebook/FacebookAuthorizationException.php';
        require_once '../application/plugins/facebook/FacebookRequestException.php';
        require_once '../application/plugins/facebook/FacebookRequest.php';
        require_once '../application/plugins/facebook/HttpClients/FacebookHttpable.php';
        require_once '../application/plugins/facebook/HttpClients/FacebookCurl.php';
        require_once '../application/plugins/facebook/HttpClients/FacebookCurlHttpClient.php';
        require_once '../application/plugins/facebook/Entities/AccessToken.php';
        $path ? $path = '../application/plugins/facebook/'.$path.'.php' : $path = false;
        $path && file_exists($path) ? require_once $path : null;
    }
}