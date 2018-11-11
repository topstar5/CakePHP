<?php

class ajaxController extends Controller
{
    public function walletBalance()
    {
        if (isset($this->user)) {
            $this->user->setNodeBalance();
            echo $this->user->getWallet()['balance'];
        } else {
            echo '0';
        }
    }
}