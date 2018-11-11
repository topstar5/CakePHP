<?php

class cronjobController extends Controller
{
    public function updateNodeBalances()
    {
        $bitcoind = $this->getBitcoind();

        if ($bitcoind) {
            $accounts = $bitcoind->listaccounts(Application::getConfig()['minconf']);

            if (is_array($accounts) && count($accounts) != 0) {
                foreach (array_keys($accounts) as $key) {
                    $prefix = substr($key, 0, 7);
                    $rest = $theRest = substr($key, 7);

                    if ($prefix == 'wallet-') {
                        $user = User::getByNickname($rest);
                        $user ? $user->setNodeBalance() : null;
                    }
                }
            }
        }
    }
}