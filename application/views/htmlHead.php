<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "https://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<!-- MADE BY ALEX WINTER -->

<head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <title><?=isset($data['question']) ? $data['question']->text.' | ' : null?> <?=Application::getConfig()['name'].' | '.output('SUBTITLE')?></title>

    <meta name="keywords" content="<?=Application::getConfig()['name']?>" />
    <meta name="description" content="<?=output('SUBTITLE')?>" />
    <meta property="og:url" content="<?=isset($data['question']) ? internalLink('question/show/'.md5($data['question']->id)) : Application::getConfig()['url']?>" />
    <meta property="og:image" content="<?=isset($data['question']) ? internalLink('uploads/questions/'.$data['question']->file) :  Application::getConfig()['url'].'/img/icons/OG.png'?>" />
    <meta property="og:title" content="<?=isset($data['question']) ? $data['question']->text : Application::getConfig()['name']?>" />
    <meta property="og:description" content="<?=output('SUBTITLE')?>" />
    <meta property="og:type" content="website" />
    <meta property="fb:app_id" content="789729057779903" />

    <link rel="stylesheet"  href="<?=Application::getConfig()['url']?>/css/default.css" type="text/css" media="all" />
    <link rel="stylesheet" href="<?=Application::getConfig()['url']?>/css/font-awesome.css">
    <link rel="stylesheet" href="<?=Application::getConfig()['url']?>/css/bootstrap.css">
    <link rel="stylesheet" href="<?=Application::getConfig()['url']?>/css/datepicker.css">
    <link rel="stylesheet" type="text/css" href="<?=Application::getConfig()['url']?>/css/dd.css" />

    <script type="application/javascript" src="<?=Application::getConfig()['url']?>/js/jquery.dd.min.js"></script>
    <script type="application/javascript" src="<?=Application::getConfig()['url']?>/js/jquery.min.js"></script>
    <script type="application/javascript" src="<?=Application::getConfig()['url']?>/js/jquery-migrate-1.2.1.min.js"></script>
    <script type="application/javascript" src="<?=Application::getConfig()['url']?>/js/jquery.form.min.js"></script>
    <script type="text/javascript" src="<?=Application::getConfig()['url']?>/js/jquery.lazyload.min.js"></script>
    <script> $(function() { $(".lazy").lazyload({ effect : "fadeIn" }); }); </script>
    <script type="application/javascript" src="<?=Application::getConfig()['url']?>/js/bootstrap.min.js"></script>
    <script type="application/javascript" src="<?=Application::getConfig()['url']?>/js/bootstrap-datepicker.js"></script>
    <script type="application/javascript" src="<?=Application::getConfig()['url']?>/plugins/jqueryCountdown/jquery.countdown.min.js"></script>
    <script type="application/javascript" src="<?=Application::getConfig()['url']?>/plugins/zeroClipboard/ZeroClipboard.min.js"></script>

    <link rel="shortcut icon" href="<?php echo Application::getConfig()['url']; ?>/img/favicon.png?v=2" type="image/x-icon" />
    <script>
        (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
        (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
        m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
        })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

        ga('create', 'UA-44492696-2', 'auto');
        ga('send', 'pageview');
    </script>
</head>