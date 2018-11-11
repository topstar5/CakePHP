<?php

class userController extends Controller
{
    public function index()
    {
        isset($_SESSION['loggedIn']) ?
            $this->view('user/index') :
            header('Location: '.Application::getConfig()['url']);
    }

    public function logout()
    {
        $_SESSION = array();
        $this->user->session = '';
        setcookie('session', $this->user->session, time() - 100, '/');
        BaseModel::replaceVar('users', array('session' => ''), array('id' => $this->user->id));
        header('Location: '.$this->config['url']);
    }

    public function register()
    {
        $data['register'] = false;
        $data['securimage'] = $this->getSecurimage();
        $data['request'] = $this->request;

        if (isset($this->request['registerSubmit'])) {

            if ($data['securimage']->check($this->request['registerCaptcha'])) {

                $data['register'] = User::register(
                    $this->request['registerEmail'],
                    $this->request['registerNickname'],
                    $this->request['registerPassword']
                );

                if ($data['register'] == true) {
                    $data['user'] = $data['register'];
                    $data['login'] = $data['user']->login(array($this->request['registerEmail'], $this->request['registerPassword']));
                    $this->alert(array('text' => 'REG_SUCCESS', 'type' => 'success'));
                } else {
                    $data['alert'] = array('type' => 'error', 'text' => output('REG_FAIL'));
                }
            } else {
                $data['alert'] = array('type' => 'error', 'text' => output('incorrect-captcha'));
            }
        } else {
            $this->initFacebook('FacebookRedirectLoginHelper');
            $fbLoginHelper = new \Facebook\FacebookRedirectLoginHelper($this->config['url'].'/user/login');
            $data['fbLoginUrl'] = $fbLoginHelper->getLoginUrl(array('scope' => 'email'));
        }

        $this->model('Question');
        $data['questions'] = Question::getAll('open');
        $this->view('home/index', $data);
    }

    public function login($param = [])
    {
        $data['login'] = '';
        $rsp = array();
        $data['request'] = $this->request;

        $this->initFacebook('FacebookRedirectLoginHelper');
        $fbLoginHelper = new \Facebook\FacebookRedirectLoginHelper($this->config['url'].'/user/login');

        if (isset($this->request['code'])) {

            try {
                $session = $fbLoginHelper->getSessionFromRedirect();
            } catch (\Facebook\FacebookRequestException $ex) {
                $data['alert'] = array('type' => 'error', 'text' => output('SOMETHING_WENT_WRONG'));
            } catch (\Exception $ex) {
                $data['alert'] = array('type' => 'error', 'text' => output('SOMETHING_WENT_WRONG'));
            }

            if ($session) {
                $request = new \Facebook\FacebookRequest($session, 'GET', '/me');
                $response = $request->execute()->getGraphObject(\Facebook\GraphUser::className());
                $facebookId = $response->getId();
                $facebookEmail = $response->getEmail();

                if (User::facebookIdExists($facebookId) == false) {
                    $data['user'] = User::register($facebookEmail, $response->getName(), $facebookId, $facebookId);
                } else {
                    $data['user'] = User::getByFacebookId($facebookId);
                }

                is_object($data['user']) ?
                    $data['login'] = $data['user']->login(array($facebookEmail, 'fb_id')) :
                    $data['alert'] = array('type' => 'error', 'text' => output('something-went-wrong'));

                $data['login'] ? header('Location: ' . $this->config['url']) : null;
            }
        }

        $data['fbLoginUrl'] = $fbLoginHelper->getLoginUrl(array('scope' => 'email'));

        if (!$param && !$session) {
            $rsp = BaseModel::fetch(array('id'), 'users', array('email' => $this->request['loginEmail']), array('LIMIT 1'));
        }

        if (count($rsp) != 0) {
            $user = new User($rsp[0]['id']);

            if ($user) {
                $data['user'] = $user;
                $data['login'] = $data['user']->login(array($this->request['loginEmail'], $this->request['loginPassword']));
            }
        } else if (isset($this->request['loginEmail'])) {
            $data['login'] = false;
        }

        $data['login'] ?
            header('Location: '.$this->config['url']) :
            $this->alert(array('text' => 'SIGN_IN_FAIL', 'type' => 'error'));
    }

    public function wallet()
    {
        $bitcoind = $this->getBitcoind();
        $data['user'] = new User($_SESSION['me']);

        if ($bitcoind) {
            $data['wallet'] = $data['user']->getWallet();

            if (isset($this->request['walletSendSubmit'])) {

                if($this->request['walletSendAmount'] <= $data['wallet']['balance']) {
                    User::generatePassword($this->request['walletSendPass']) == $data['user']->pass || $data['user']->hasNoPassword() ?
                        $tx = $data['user']->sendFromWallet($this->request['walletSendRecipient'], $this->request['walletSendAmount']) :
                        $tx = false;
                    $tx ?
                        $data['alert'] = array('type' => 'success', 'text' => output('tx-successful').' (<a target="_blank" href="'.linkToTx($tx).'">'.$tx.'</a>)') :
                        $data['alert'] = array('type' => 'error', 'text' => output('something-went-wrong'));
                } else {
                    $data['alert'] = array('type' => 'error', 'text' => output('insufficient-funds'));
                }

                $data['wallet'] = $data['user']->getWallet();
            }
        }

        if($bitcoind != false){
            $data['title'] = array('title' => output('your-wallet'), 'icon' => 'fa fa-bitcoin');
            $this->view('user/wallet', $data);
        }else{
            $data['alert'] = array('type' => 'error', 'text' => output('something-went-wrong'));
            $this->view('trade/index', $data);
        }
    }

    public function resetPass($code = null, $email = null)
    {
        if (!$code) {

            if (isset($this->request['resetPassSubmit'])) {
                $data['user'] = User::getByEmail($this->request['resetPassEmail']);

                if ($data['user']) {
                    BaseModel::email($data['user']->emailNotification('resetPass'));
                    $this->alert(array('text' => 'CHECK_YOUR_MAIL', 'type' => 'info'));
                } else {
                    $this->alert(array('text' => 'SOMETHING_WENT_WRONG', 'type' => 'error'));
                }
            }
        } else if ($code && $email) {
            $data['user'] = User::getByEmail($email);

            if ($data['user']) {
                $data['newPass'] = $data['user']->resetPass($code);

                if ($data['newPass'] != false) {
                    BaseModel::email($data['user']->emailNotification('newPass', $data));
                    $this->alert(array('text' => 'CHECK_YOUR_MAIL', 'type' => 'success'));
                } else {
                    $this->alert(array('text' => 'SOMETHING_WENT_WRONG', 'type' => 'error'));
                }
            }
        }
    }

    public function changePass()
    {
        $allowedToChangePass =
            (User::generatePassword($this->request['changePassOldPass']) == $this->user->password || $this->user->hasNoPassword())
            && $this->request['changePassNewPass'] == $this->request['changePassNewPassConfirm'];
        $allowedToChangePass ?
            $changePass = $this->user->changePass($this->request['changePassNewPass']) :
            $changePass = false;
        $changePass ?
            $this->alert(array('text' => 'PASS_CHANGED', 'type' => 'success')) :
            $this->alert(array('text' => 'SOMETHING_WENT_WRONG', 'type' => 'error'));
    }

    public function withdraw($withdrawalId_md5 = null, $hash = null)
    {
        $this->model('Withdrawal');

        if (isset($this->request['withdrawSubmit'])) {
            $success = Withdrawal::addWithdrawal($this->user, $this->request['withdrawAddress'], $this->request['withdrawAmount']);
            $success ?
                $this->alert(array('text' => 'WITHDRAW_SUCCESS', 'type' => 'info')) :
                $this->alert(array('text' => 'WITHDRAW_FAIL', 'type' => 'error'));
        } else if ($withdrawalId_md5) {
            $withdrawal = Withdrawal::getByMd5($withdrawalId_md5);
            $withdrawal ? $tx = $withdrawal->send($hash) : $tx = false;
            $_SESSION['wallet'] = $this->user->getWallet();
            $tx ?
                $this->alert(array('text' => 'WITHDRAW_SENT', 'type' => 'success')) :
                $this->alert(array('text' => 'SOMETHING_WENT_WRONG', 'type' => 'error'));
        }
    }

    public function emailNotificationSettings()
    {
        if (isset($this->request['inquiryEmail'])) {
            $inquiryEmail = 1;
        } else {
            $inquiryEmail = 0;
        }
        if (isset($this->request['rewardEmail'])) {
            $rewardEmail = 1;
        } else {
            $rewardEmail = 0;
        }
        if (isset($this->request['answerEmail'])) {
            $answerEmail = 1;
        } else {
            $answerEmail = 0;
        }

        $user_id = $this->user->getUserId();

        $notificationUpdate = User::updateEmailNotification($inquiryEmail, $rewardEmail, $answerEmail, $user_id);

        $notificationUpdate ?
            $this->alert(array('text' => 'NOTIFICATION_UPDATED', 'type' => 'success')) :
            $this->alert(array('text' => 'SOMETHING_WENT_WRONG', 'type' => 'error'));
    }
}