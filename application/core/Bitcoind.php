<?php

function bitcoind()
{
    require_once('../application/plugins/easybitcoin/easybitcoin.php');

    $bitcoinduser = 'generated_by_armory';
    $bitcoindpass = '46UCPaZhgNZMq6sYcRJiGbiWT34MCLWNbfmMg4WtB48q';
    $bitcoindhost = 'panagot.ddns.net';
    $bitcoindport = '8332';

    $bitcoin = @new Bitcoin($bitcoinduser, $bitcoindpass, $bitcoindhost, $bitcoindport);
    $bitcoindhost != 'localhost' ? $bitcoin->setSSL() : null;

    if($bitcoin->getinfo() != false){
        return $bitcoin;
    }else{
	return false;
    }
}