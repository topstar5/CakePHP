<?php

class Withdrawal extends BaseModel
{
    public $id;
    public $address;
    public $amount;
    public $user_id;
    public $created;
    public $sent;
    public $txid;

    public function __construct($id)
    {
        $query = BaseModel::fetch(array('*'), 'withdrawals', array('id' => $id), array('LIMIT 1'));

        if (count($query) != 0) {
            $this->set_object_vars($this, $query[0]);
            return $this;
        } else {
            return false;
        }
    }

    public static function getByMd5($withdrawalId_md5)
    {
        $withdrawal = BaseModel::getByMd5Id('withdrawals', $withdrawalId_md5);
        $withdrawal ? $withdrawal = new Withdrawal($withdrawal['id']) : $withdrawal = false;
        return $withdrawal;
    }

    public static function addWithdrawal($user, $address, $amount)
    {
        $error = false;
        $amount = btc($amount);
        $bitcoind = BaseModel::getBitcoind();
        $wallet = $user->getWallet();
        $amount <= $wallet['balance'] ? null : $error = true;
        $bitcoind ? null : $error = true;
        validAddress($address) ? null : $error = true;

        if (!$error) {

            $vals = array(
                'address' => $address,
                'amount' => $amount,
                'user_id' => $user->id,
                'created' => now()
            );

            $query = BaseModel::insert('withdrawals', $vals);

            if ($query) {
                $data['withdrawal'] = $user->getWithdrawals()[0];
                BaseModel::email($user->emailNotification('withdrawalUrl', $data));
                return true;
            }
        }

        return false;
    }

    public function checkHash($hash)
    {
        return $hash == $this->getHash();
    }

    public function send($hash)
    {
        $bitcoind = $this->getBitcoind();

        if (empty($this->txid) && $bitcoind && $this->checkHash($hash)) {
            $user = new User($this->user_id);
            $bitcoind->walletpassphrase(Application::getConfig()['walletPass'], 60);
            $tx = $bitcoind->sendfrom($user->walletAccount(), $this->address, btc($this->amount), Application::getConfig()['minconf'], 'Withdrawal');
            $bitcoind->walletlock();

            if ($tx) {
                $this->sent = now();
                $this->txid = $tx;
                BaseModel::replaceVar('withdrawals', array('sent' => $this->sent, 'txid' => $this->txid), array('id' => $this->id));
                $user->setNodeBalance();
                return true;
            }
        }

        return false;
    }

    public function getHash()
    {
        $hash = hash_hmac('sha256', $this->amount.$this->created.$this->address.$this->user_id, Application::getConfig()['salt']);
        return $hash;
    }

    public function getWithdrawUrl()
    {
        $url = Application::getConfig()['url'].'/user/withdraw/';
        $url .= md5($this->id).'/';
        $url .= $this->getHash();
        return $url;
    }
}