<?php

function config()
{
    $cfg = array(
        'name' => 'Bitfortip',
        'email' => 'info@bitfortip.com',
        'supportEmail' => 'support@bitfortip.com',
        'supportedLanguages' => array('en_US'),
        'defaultLang' => 'en_US',
        'timeZone' => 'Europe/Athens',
        'dateFormat' => 'Y-m-d',
        'dateTimeFormat' => 'H:i d.m.Y',
        'url' => 'https://www.bitfortip.com',
        'loginSessionSeconds' => 60 * 60 * 24 * 7,
        'minReward' => 0.0001,
        'minconf' => 1,
        'maintenance' => 0,
        'salt' => 'SWuU.lxQ{EM[sn=$.k{HNs?aF?Hjg-hP?>DWS~#75@QWV!x+`O+NR|L%Q%k*E37}',
        'walletPass' => 'e62cf440d354da1ab0961fd1debd376870cb555f57444c41d77c6a181ba10f61',
        'pageMax' => 10
    );

    return $cfg;
}

