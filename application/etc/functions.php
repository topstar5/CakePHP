<?php

function now()
{
    $config = Application::getConfig();
    date_default_timezone_set($config['timeZone']);
    return date('Y-m-d H:i:s', time());
}

function usd($usd)
{
    return number_format(round($usd, 2),2,".",",");
}

function eur($eur)
{
    return number_format(round($eur, 2),2,",",".");
}

function vnd($vnd)
{
    return number_format(round($vnd, -2),0,".",",");
}

function btc($btc)
{
    return (real)number_format($btc,8,".",",");
}

function fiatCurrency()
{
    $config = config();
    return $config['currency']['short'];
}

function fiat($fiat)
{
    switch (fiatCurrency()){
        case "USD":
            return usd($fiat);
        case "VND":
            return vnd($fiat);
        case "EUR":
            return eur($fiat);
    }
}

function roundFiat()
{
    if (fiatCurrency() == 'VND'){
        return -2;
    }else{
        return 2;
    }
}

function encodeHex($dec)
{
    $chars="0123456789ABCDEF";
    $return="";

    while (bccomp($dec,0)==1){
        $dv=(string)bcdiv($dec,"16",0);
        $rem=(integer)bcmod($dec,"16");
        $dec=$dv;
        $return=$return.$chars[$rem];
    }
    return strrev($return);
}

function decodeBase58($base58)
{
    $origbase58=$base58;

    $chars="123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz";
    $return="0";

    for($i=0;$i<strlen($base58);$i++){
        $current=(string)strpos($chars,$base58[$i]);
        $return=(string)bcmul($return,"58",0);
        $return=(string)bcadd($return,$current,0);
    }

    $return=encodeHex($return);

    for($i=0;$i<strlen($origbase58)&&$origbase58[$i]=="1";$i++){
        $return="00".$return;
    }

    if(strlen($return)%2!=0){
        $return="0".$return;
    }

    return $return;
}

function validAddress($addr,$addressversion=ADDRESSVERSION)
{
    $addr=decodeBase58($addr);

    if(strlen($addr)!=50){
        return false;
    }
    $version=substr($addr,0,2);

    if(hexdec($version)>hexdec($addressversion)){
        return false;
    }

    $check=substr($addr,0,strlen($addr)-8);
    $check=pack("H*" , $check);
    $check=strtoupper(hash("sha256",hash("sha256",$check,true)));
    $check=substr($check,0,8);
    return $check==substr($addr,strlen($addr)-8);
}

function getIP()
{
    $ipaddress = '';
    if (!empty($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if(!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if(!empty($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if(!empty($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if(!empty($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if(!empty($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}

function internalLink($to)
{
    $config = config();
    return $config['url'].'/'.$to;
}

function linkToTx($txid)
{
    $url = 'https://blockchain.info/tx/'.$txid;
    return $url;
}

function linkToAddress($address)
{
    $url = 'https://blockchain.info/address/'.$address;
    return $url;
}

function validateDate($date)
{
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') == $date;
}