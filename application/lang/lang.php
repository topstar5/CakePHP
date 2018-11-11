<?php

function lang()
{
    $config = config();
    $lang = Application::getConfig()['defaultLang'];

    if(isset($_COOKIE['lang']) && in_array($_COOKIE['lang'], Application::getConfig()['supportedLanguages'])) {
        $lang = $_COOKIE['lang'];
    } else if(!empty($_SESSION['loggedIn']) && in_array($_SESSION['me']->lang, $config['supportedLanguages'])) {
        $lang = $_SESSION['me']->lang;
    }

    return (string)$lang;
}

function output($key)
{
    $lang = lang();
    $config = Application::getConfig();
    $path = '../application/lang/languageFiles/'.$lang.'.php';

    if(file_exists($path)) {
        include '../application/lang/languageFiles/' . $lang . '.php';
    }else{
        include '../application/lang/languageFiles/en_US.php';
    }

    if(array_key_exists($key, $output)){
        return $output[$key];
    }else{
        include '../application/lang/languageFiles/en_US.php';

        if(array_key_exists($key, $output)){
            return $output[$key];
        }else{
            return $key;
        }
    }
}

function mailOutput($lang, $key)
{
    $config = config();
    include 'languageFiles/'.$lang.'.php';

    if(array_key_exists($key, $output)){
        return $output[$key];
    }else{
        include 'languageFiles/en_US.php';

        if(array_key_exists($key, $output)){
            return strip_tags($output[$key]);
        }else{
            return $key;
        }
    }
}