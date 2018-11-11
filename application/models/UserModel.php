<?php

class User extends BaseModel
{
    public $id;
    public $admin;
    public $email;
    public $nickname;
    public $password;
    public $nodeBalance;
    public $nodeAddress;
    public $lang;
    public $fb_id;
    public $ip_address;
    public $last_login;
    public $last_action;
    public $session;
    public $created;

    public function __construct($id)
    {
        $query = BaseModel::fetch(array('*'), 'users', array('id' => $id), array('LIMIT 1'));

        if (count($query) != 0) {
            $this->set_object_vars($this, $query[0]);
            return $this;
        } else {
            return false;
        }
    }

    public function login($params = [])
    {
        if ($this->password == User::generatePassword($params[1]) || $params[1] == 'fb_id'){
            $_SESSION['me'] = $this->id;
            $_SESSION['loggedIn'] = true;
            $this->setLoginVars();
            return true;
        } else {
            $_SESSION = [];
            return false;
        }
    }

    public function setLoginVars()
    {
        $this->updateIP();
        $this->setUserIdCookie();
        $this->setLastLogin();
        $this->setSessionCookie();
        $this->setNodeBalance();
    }

    public function setUserIdCookie()
    {
        setcookie('userId', $this->id, time()+1*365*24*60*60, '/');
        return true;
    }

    public function setSessionCookie()
    {
        $this->session = md5($this->last_login.$this->ip_address);
        BaseModel::replaceVar('users', array('session' => $this->session), array('id' => $this->id));
        setcookie('session', $this->session, time() + Application::getConfig()['loginSessionSeconds'], '/');
        return true;
    }

    public static function setLangCooke($lang)
    {
        $config = config();

        if(!empty($lang) && in_array($lang, $config['supportedLanguages'])) {
            setcookie('lang', $lang, time() + 1 * 365 * 24 * 60 * 60, '/');
        }
    }

    public function setLastLogin()
    {
        $this->last_login = now();
        BaseModel::replaceVar('users', array('last_login' => $this->last_login), array('id' => $this->id));
        $this->updateLastAction();
        return true;
    }

    public function updateIP()
    {
        $this->ip_address = getIP();
        BaseModel::replaceVar('users', array('ip_address' => $this->ip_address), array('id' => $this->id));
        return true;
    }

    public function updateLastAction()
    {
        $this->last_action = now();
        BaseModel::replaceVar('users', array('last_action' => $this->last_action), array('id' => $this->id));
        return true;
    }

    public function setLang($lang)
    {
        $config = config();

        if(!empty($lang) && in_array($lang, $config['supportedLanguages'])){
            $this->lang = $lang;
            BaseModel::replaceVar('users', array('lang' => $this->lang), array('id' => $this->id));
            User::setLangCooke($lang);
            return true;
        } else {
            return false;
        }
    }

    public function setNodeBalance()
    {
        $bitcoind = $this->getBitcoind();

        if ($bitcoind) {
            $this->nodeBalance = $bitcoind->getbalance($this->walletAccount(), Application::getConfig()['minconf']);
            BaseModel::replaceVar('users', array('nodeBalance' => $this->nodeBalance), array('id' => $this->id));
            return true;
        }

        return false;
    }

    public function setNodeAddress()
    {
        if (empty($this->nodeAddress)) {
            $bitcoind = $this->getBitcoind();

            if ($bitcoind) {
                $this->nodeAddress = $bitcoind->getnewaddress($this->walletAccount());
                BaseModel::replaceVar('users', array('nodeAddress' => $this->nodeAddress), array('id' => $this->id));
                return true;
            }
        }

        return false;
    }

    public static function me($var)
    {
        if($_SESSION['loggedIn'] && isset($_SESSION['me'])){
            $user = new User($_SESSION['me']);

            if($user){
                return $user->$var;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    public static function generatePassword($password)
    {
        return hash_hmac('sha256', (string)trim($password), Application::getConfig()['salt']);
    }

    public function hasNoPassword()
    {
        $hasNoPassword = $this->password == User::generatePassword($this->fb_id);
        return $hasNoPassword;
    }

    public function walletAccount()
    {
        return 'wallet-'.$this->nickname;
    }

    public function getQuestions()
    {
        $questions = array();
        $query = BaseModel::fetch(array('id'), 'questions', array('user_id' => $this->id), array('ORDER BY created DESC'));

        if ($query && count($query) != 0) {
            foreach ($query as $row) {
                $questions[] = new Question($row['id']);
            }
        }

        return $questions;
    }

    public function getWithdrawals()
    {
        $withdrawals = array();
        $query = BaseModel::fetch(array('id'), 'withdrawals', array('user_id' => $this->id), array('ORDER BY created DESC'));

        if ($query && count($query) != 0) {
            foreach ($query as $row) {
                $withdrawals[] = new Withdrawal($row['id']);
            }
        }

        return $withdrawals;
    }

    public function getUploads()
    {
        $config = config();
        $target_path = $config['uploadDir'].'/'.$this->id.'/';
        $files = [];

        if (is_dir($target_path)) {
            if ($handle = opendir($target_path)) {
                while (($file = readdir($handle)) !== false) {
                    if (!in_array($file, array('.', '..')) && !is_dir($target_path . $file)) {
                        $files[] = internalLink($target_path.$file);
                    }
                }
            }
        }

        return $files;
    }

    public function reservedCoins()
    {
        $query = BaseModel::fetch(array('SUM(reward)'), 'questions', array('user_id' => $this->id, 'closed' => '0'));
        $query ? $reservedCoins = $query[0]['SUM(reward)'] : $reservedCoins = 0;
        $query = BaseModel::fetch(array('SUM(amount)'), 'withdrawals', array('user_id' => $this->id, 'txid' => ''));
        $query ? $reservedCoins = $reservedCoins + $query[0]['SUM(amount)'] : null;
        return btc($reservedCoins);
    }

    public function getWallet()
    {
        $reservedCoins = $this->reservedCoins();
        empty($this->nodeBalance) ? $this->setNodeBalance() : null;
        empty($this->nodeAddress) ? $this->setNodeAddress() : null;
        $balancePure = $this->nodeBalance;
        $balanceReservations = $balancePure - $reservedCoins;

        $wallet = array(
            'balancePure' => $balancePure,
            'balance' => $balanceReservations,
            'address' => $this->nodeAddress,
        );

        $wallet['balance'] < 0 ? $wallet['balance'] = (float)0 : null;
        return $wallet;
    }

    public function sendFromWallet($recipient, $amount)
    {
        $bitcoind = $this->getBitcoind();

        if ($bitcoind) {
            $walletAccount = $this->walletAccount();
            $wallet = $this->getWallet();

            if ($amount <= $wallet['balance']) {

                if (checkAddress($recipient)) {
                    $bitcoind->walletpassphrase(Application::getConfig()['walletPass'], 120);
                    $tx = $bitcoind->sendfrom($walletAccount, $recipient, btc($amount));
                    $bitcoind->walletlock();
                    return $tx;
                } else {
                    $offchainRecipient = User::getByEmail($recipient);
                    $offchainRecipient ?
                        $tx = $bitcoind->move($this->walletAccount(), $offchainRecipient->walletAccount(), btc($amount), Application::getConfig()['minconf']) :
                        $tx = false;
                    return $tx;
                }
            }
        }

        return false;
    }

    public function emailNotification($type, $data = [])
    {
        $data['subject'] = '['.Application::getConfig()['name'].'] ';
        $data['message'] = '';
        $data['user'] = $this;

        switch ($type) {
            case 'withdrawalUrl':
                $data['subject'] .= ucfirst(mailOutput($this->lang, 'CONFIRM_WITHDRAW'));
                $data['message'] .= '<p>'.lcfirst(mailOutput($this->lang, 'Confirm your withdrawal')).'.</p>';
                $data['message'] .= '<p>'.$data['withdrawal']->amount.' BTC => '.$data['withdrawal']->address.'</p>';
                $data['message'] .= '<p>'.mailOutput($this->lang, 'YOUR_WITHDRAW_URL').'</p>';
                $data['message'] .= '<p><a href="'.$data['withdrawal']->getWithdrawUrl().'">'.$data['withdrawal']->getWithdrawUrl().'</a><br>';
                break;
            case 'resetPass':
                $data['subject'] .= ucfirst(mailOutput($this->lang, 'PASSW_RESET'));
                $data['message'] .= '<p>'.lcfirst(mailOutput($this->lang, 'PASSW_RESET_REQUESTED')).'.</p>';
                $data['message'] .= 'function<p><a href="'.$this->resetPassUrl().'">'.$this->resetPassUrl().'</a></p>';
                break;
            case 'newPass':
                $data['subject'] .= ucfirst(mailOutput($this->lang, 'YOUR_NEW_PASSW'));
                $data['message'] .= '<p>'.lcfirst(mailOutput($this->lang, 'YOUR_NEW_PASSW')).':</p>';
                $data['message'] .= '<p><b>'.$data['newPass'].'</b></p>';
                $data['message'] .= '<p>'.mailOutput($this->lang, 'PLS_CHANGE_PASSW').'</p>';
                break;
            case 'awardReceived':
                $data['subject'] .= ucfirst(mailOutput($this->lang, 'REWARD_RECEIVED'));
                $data['message'] .= '<p>'.lcfirst(mailOutput($this->lang, 'REWARD_RECEIVED')).'</p>';
                $data['message'] .= '<p>'.mailOutput($this->lang, 'QUESTION').':<br>'.$data['question']->text.'</p>';
                $data['message'] .= '<p>'.mailOutput($this->lang, 'AMOUNT').': '.$data['question']->reward.' BTC</p>';
                break;
            case 'newInquiry':
                $data['subject'] .= ucfirst(mailOutput($this->lang, 'NEW_INQUIRY'));
                $data['message'] .= '<p>'.lcfirst(mailOutput($this->lang, 'NEW_INQUIRY')).'</p>';
                $data['message'] .= '<p>'.mailOutput($this->lang, 'QUESTION').':<br>'.$data['text'].'<br><br>'.mailOutput($this->lang, 'REWARD').': '.$data['reward'].' BTC<br>'.mailOutput($this->lang, 'LINK').'<br>'.internalLink('question/show/').''.$data['hashed_id'].'<br><br>'.mailOutput($this->lang, 'UNSUBSCRIBE').': '.internalLink('user').'</p>';
                break;
            case 'inquiryAnswer':
                $data['subject'] .= ucfirst(mailOutput($this->lang, 'INQUIRY_ANSWER'));
                $data['message'] .= '<p>'.lcfirst(mailOutput($this->lang, 'INQUIRY_ANSWER')).'</p>';
                $data['message'] .= '<p>'.mailOutput($this->lang, 'ANSWER').':<br>'.$data['text'].'<br><br>'.mailOutput($this->lang, 'LINK').'<br>'.internalLink('question/show/').''.$data['hashed_id'].'<br><br><br>'.mailOutput($this->lang, 'UNSUBSCRIBE').': '.internalLink('user').'</p>';
                break;
            case 'bitcoinWin':
                $data['subject'] .= ucfirst(mailOutput($this->lang, 'BITCOIN_WIN'));
                $data['message'] .= '<p>'.mailOutput($this->lang, 'BITCOIN_WIN_MESSAGE').': '.$data['reward'].' BTC<br>'.mailOutput($this->lang, 'QUESTION').':<br>'.$data['text'].'<br><br>'.mailOutput($this->lang, 'LINK').'<br>'.internalLink('question/show/').''.$data['hashed_id'].'</p>';
                break;
            case 'newInquiryApproval':
                $data['subject'] .= ucfirst(mailOutput($this->lang, 'NEW_INQUIRY_APPROVAL_TITLE'));
                $data['message'] .= '<p>'.lcfirst(mailOutput($this->lang, 'NEW_INQUIRY_APPROVAL')).'</p>';
                $data['message'] .= '<p>'.mailOutput($this->lang, 'QUESTION').':<br>'.$data['text'].'<br><br>'.mailOutput($this->lang, 'REWARD').': '.$data['reward'].' BTC<br>'.mailOutput($this->lang, 'LINK').'<br>'.internalLink('question/show/').''.$data['hashed_id'].'<br><br>'.mailOutput($this->lang, 'UNSUBSCRIBE').': '.internalLink('user').'</p>';
                break;
        }

        return $data;
    }

    public function changePass($newPass)
    {
        $newPass = User::generatePassword($newPass);
        BaseModel::replaceVar('users', array('password' => $newPass), array('id' => $this->id));
        $this->password = $newPass;
        return true;
    }

    public function resetPass($code)
    {
        if ($code == $this->resetPassCode()) {
            $newPassString = substr(str_shuffle("1234567890qwertzuiopasdfghjklyxcvbnmQWERTZUIOPASDFGHJKLYXCVBNM"), 0, 8);
            $newPassDb = User::generatePassword($newPassString);
            BaseModel::replaceVar('users', array('password' => $newPassDb), array('id' => $this->id));
            $this->password = $newPassDb;
            return $newPassString;
        } else {
            return false;
        }
    }

    public function resetPassCode()
    {
        $code = md5(date('Y-m-d', strtotime(now())).Application::getConfig()['salt'].$this->email);
        return $code;
    }

    public function resetPassUrl()
    {
        $config = config();
        $url = $config['url'].'/user/resetPass/'.$this->resetPassCode().'/'.$this->email;
        return $url;
    }

    public static function register($email, $nickname, $password, $fb_id = [])
    {
        $vars['email'] = filter_var($email, FILTER_SANITIZE_EMAIL);
        $vars['nickname'] = $nickname;
        is_string($fb_id) ? $vars['fb_id'] = $fb_id : null;

        if (filter_var($vars['email'], FILTER_VALIDATE_EMAIL)) {
            $emailExists = BaseModel::fetch(array('*'), 'users', array('email' => $vars['email']), array('LIMIT 1'));
            $nicknameExists = BaseModel::fetch(array('*'), 'users', array('nickname' => $vars['nickname']), array('LIMIT 1'));

            if (count($emailExists) == 0 && count($nicknameExists) == 0) {
                $vars['password'] = User::generatePassword($password);
                $vars['created'] = now();
                $vars['lang'] = lang();
                $query = BaseModel::insert('users', $vars);

                if ($query) {
                    $query = BaseModel::fetch(array('id'), 'users', array('nickname' => $nickname, 'email' => $email), array('LIMIT 1'));
                    $user = new User($query[0]['id']);
                    return $user;
                }
            }
        }

        return false;
    }

    public static function search($searchQuery)
    {
        $users = [];

        $varsArray = array(
            'code' => $searchQuery,
            'email' => $searchQuery,
            'name' => $searchQuery,
            'id_pass_number' => $searchQuery,
            'phone' => $searchQuery,
            'affiliate_code' => $searchQuery,
            'ip_address' => $searchQuery
        );

        $query = BaseModel::fetch(array('code'), 'users', $varsArray, array(), true);

        if (count($query) != 0) {
            foreach ($query as $row) {
                $users[] = new User($row['code']);
            }
        }

        return $users;
    }

    public static function getByFacebookId($facebookId)
    {
        $query = BaseModel::fetch(array('id'), 'users', array('fb_id' => $facebookId), array('LIMIT 1'));
        count($query[0]) != 0 ? $user = new User($query[0]['id']) : $user = false;
        return $user;
    }

    public static function facebookIdExists($facebookId)
    {
        $query = BaseModel::fetch(array('id'), 'users', array('fb_id' => $facebookId));
        count($query) == 0 ? $rsp = false : $rsp = true;
        return $rsp;
    }

    public static function getByEmail($email)
    {
        $query = BaseModel::fetch(array('*'), 'users', array('email' => $email), array('LIMIT 1'));
        count($query) != 0 ? $user = new User($query[0]['id']) : $user = false;
        return $user;
    }

    public static function getByNickname($nickname)
    {
        $query = BaseModel::fetch(array('*'), 'users', array('nickname' => $nickname), array('LIMIT 1'));
        count($query) != 0 ? $user = new User($query[0]['id']) : $user = false;
        return $user;
    }

    public function getUserId(){
        return $this->id;
    }

    public static function updateEmailNotification($inquiryEmail, $rewardEmail, $answerEmail, $user_id)
    {
        BaseModel::replaceVar('users', array('inquiry_email' => $inquiryEmail, 'bitcoin_win_email' => $rewardEmail, 'answer_inquiry_email' => $answerEmail), array('id' => $user_id));
        return true;
    }
}