<?php

class View
{
    public static function render($path, $data = [])
    {
        View::renderContent($path, $data);
    }

    public static function renderContent($path, $data = [])
    {
        ?>

        <div id="fb-root"></div>
        <script>(function(d, s, id) {
                var js, fjs = d.getElementsByTagName(s)[0];
                if (d.getElementById(id)) return;
                js = d.createElement(s); js.id = id;
                js.src = "//connect.facebook.net/<?=lang()?>/sdk.js#xfbml=1&appId=789729057779903&version=v2.0";
                fjs.parentNode.insertBefore(js, fjs);
            }(document, 'script', 'facebook-jssdk'));</script>
        <?php

        $data['alert'] = isset($data['alert']) ?
            View::alert($data['alert']['type'], $data['alert']['text']) :
            null;
        require_once '../application/views/alert.php';

        ?>

        <div class="container">
            <div id="content">
                <h1 class="page-header">
                    <a href="<?=$data['config']['url']?>">
                        <img src="<?=internalLink('img/logo.png')?>" alt="<?=$data['config']['name']?>">
                    </a>
                    <small>
                        <?=output('SUBTITLE')?>
                    </small>
                </h1>
                <div class="header-bar" style="margin-bottom: 5%;">
                    <span class="pull-right">
                        <a class="text-muted" data-toggle="tooltip" title="<?=output('FAQ')?>" href="<?=internalLink('faq')?>">
                            <i class="fa fa-question-circle"></i>
                        </a>
                    </span>
                    <span style="margin-right:0.8%;">
                        <a href="<?=internalLink('home')?>"><img src="<?=Application::getConfig()['url']?>/img/flags/shiny/32/United-States.png" alt="English"> <?=output('ENGLISH')?></a>
                    </span>
                    <span style="margin-right:0.8%;">
                        <a href="<?=internalLink('home/?lang=ge')?>"><img src="<?=Application::getConfig()['url']?>/img/flags/shiny/32/Germany.png" alt="German"> <?=output('GERMAN')?></a>
                    </span>
                    <span style="margin-right:0.8%;">
                        <a href="<?=internalLink('home/?lang=fr')?>"><img src="<?=Application::getConfig()['url']?>/img/flags/shiny/32/France.png" alt="French"> <?=output('FRENCH')?></a>
                    </span>
                    <span style="margin-right:0.8%;">
                        <a href="<?=internalLink('home/?lang=it')?>"><img src="<?=Application::getConfig()['url']?>/img/flags/shiny/32/Italy.png" alt="Italian"> <?=output('ITALIAN')?></a>
                    </span>
                    <span style="margin-right:0.8%;">
                        <a href="<?=internalLink('home/?lang=gr')?>"><img src="<?=Application::getConfig()['url']?>/img/flags/shiny/32/Greece.png" alt="Greek"> <?=output('GREEK')?></a>
                    </span>
                    <span style="margin-right:0.8%;">
                        <a href="<?=internalLink('home/?lang=sp')?>"><img src="<?=Application::getConfig()['url']?>/img/flags/shiny/32/Spain.png" alt="Spanish"> <?=output('SPANISH')?></a>
                    </span>
                    <span style="margin-right:0.8%;">
                        <a href="<?=internalLink('home/?lang=po')?>"><img src="<?=Application::getConfig()['url']?>/img/flags/shiny/32/Portugal.png" alt="Portuguese"> <?=output('PORTUGUESE')?></a>
                    </span>
                    <span style="margin-right:0.8%;">
                        <a href="<?=internalLink('home/?lang=ru')?>"><img src="<?=Application::getConfig()['url']?>/img/flags/shiny/32/Russia.png" alt="Russian"> <?=output('RUSSIAN')?></a>
                    </span>
                    <span style="margin-right:0.8%;">
                        <a href="<?=internalLink('home/?lang=sw')?>"><img src="<?=Application::getConfig()['url']?>/img/flags/shiny/32/Sweden.png" alt="Swedish"> <?=output('SWEDISH')?></a>
                    </span>
                    <span style="margin-right:0.8%;">
                        <a href="<?=internalLink('home/?lang=ch')?>"><img src="<?=Application::getConfig()['url']?>/img/flags/shiny/32/China.png" alt="Chinese"> <?=output('CHINESE')?></a>
                    </span>
                    <span style="margin-right:0.8%;">
                        <a href="<?=internalLink('home/?lang=ja')?>"><img src="<?=Application::getConfig()['url']?>/img/flags/shiny/32/Japan.png" alt="Japanese"> <?=output('JAPANESE')?></a>
                    </span>
                </div>
                <div class="row">
                    <div class="col-md-8">
                        <?php

                        isset($data['title']) ?
                            View::title($data['title']['title'], $data['title']['icon']) :
                            null;

                        file_exists($path) ?
                            require_once $path :
                            require_once '../application/views/error404.php';

                        ?>
                    </div>
                    <div class="col-md-4">
                        <?php require_once '../application/views/sidebar.php'; ?>
                    </div>
                </div>
                <?php require_once '../application/views/footer.php'; ?>
            </div>
        </div>

        <?php
        require_once '../application/views/js.php';
    }

    public static function emailContent($message)
    {
        $config = config();
        $mailheader  = "From: ".$config['name']." <".$config['email'].">\r\n";
        $mailheader .= "Reply-To: ".$config['name']." <".$config['replyToEmail'].">\r\n";
        $mailheader .= "Return-Path: ".$config['email']."\r\n";
        $mailheader .= "MIME-Version: 1.0\r\n";
        $mailheader .= "Content-Type: text/html; charset=UTF-8\r\n";
        $mailheader .= "Content-Transfer-Encoding: 8bit\r\n";
        $mailheader .= "Message-ID: <".strtotime(now())." ".$config['email'].">\r\n";
        $mailheader .= "X-Mailer: PHP v".phpversion()."\r\n\r\n";

        $mailfooter = $config['companyName'].'<br>';
        $mailfooter .= $config['street'].'<br>';
        $mailfooter .= $config['postal'].'<br>';
        $mailfooter .= $config['city'];

        $template = file_get_contents('../public/emailTemplate.html');
        $text = str_replace('###CONTENT###', nl2br($message), $template);
        $text = str_replace('###STYLESHEET###', nl2br($config['url'].'/css/bootstrap.css'), $text);
        $text = str_replace('###LOGO###', nl2br($config['url'].'/img/logo.png'), $text);
        $text = str_replace('###NAME###', nl2br($config['name']), $text);
        $text = str_replace('###URL###', nl2br($config['url']), $text);
        $text = str_replace('###TIME###', nl2br(date($config['dateTimeFormat'], strtotime(now()))), $text);
        $text = str_replace('###FOOTER###', nl2br($mailfooter), $text);
        $rsp = array('header' => $mailheader, 'text' => $text);
        return $rsp;
    }

    public static function title($title, $icon = [])
    {
        ?>
        <h3 id="contentTitle" class="page-header">
            <i class="<?=$icon?>"></i> <?=$title?>
        </h3>
        <?php
    }

    public static function alert($type, $text)
    {
        $class = 'alert-info';

        switch($type){
            case 'error':
                $class = 'alert-danger';
                break;
            case 'success':
                $class = 'alert-success';
                break;
            case 'warning':
                $class = 'alert-warning';
                break;
        }

        return array('class' => $class, 'text' => $text);
    }

    public static function shortStr($string, $maxchars)
    {
        if (strlen($string) > ($maxchars)) {
            $string = iconv_substr($string, 0, $maxchars, 'UTF-8') . '...';
        }

        return $string;
    }

    public static function img($path, $attributes = [])
    {
        $config = config();
        $cls = '';
        $attr = '';

        if (count($attributes) != 0) {
            foreach (array_keys($attributes) as $key) {
                $key != 'class' ? $attr .= ' '.$key.'="'.$attributes[$key].'"' : $cls .= ' '.$attributes[$key];
            }
        }

        ?>
        <img class="lazy <?=$cls?>" data-original="<?=$path?>" <?=$attr?>>
        <noscript><img class="<?=$cls?>" src="<?=$path?>" <?=$attr?>></noscript>
        <?php
    }

    public static function flag($file, $type = null, $size = null)
    {
        !$type ? $type = 'shiny' : null;
        !$size ? $size = '16' : null;
        $path = 'flags/'.$type.'/'.$size.'/'.$file;
        View::img($path);
    }

    public static function QRcode($text, $size)
    {
        include '../application/plugins/phpqrcode/qrlib.php';
        $config = config();
        $text = urlencode(trim($text));
        $dir = '../public/tmp/qr/'.$text.'.png';
        $fileName = explode('/', $dir);
        $fileName = end($fileName);


        if (!file_exists($dir)) {
            QRcode::png(urldecode($text), $dir, QR_ECLEVEL_L, $size, 0);
        }

        ?>
        <img class="img-responsive" src="<?=$config['url']?>/tmp/qr/<?=urlencode($fileName)?>">
        <?php
    }

    public static function onSpotEdit($val, $name, $id)
    {
        $dataContent = '<div class=\'input-group\'>';
        $dataContent .= '<input type=\'text\' class=\'form-control\' name=\''.$name.'\' value=\''.$val.'\' required=\'true\'>';
        $dataContent .= '<input type=\'hidden\' name=\'value\' value=\''.$name.'\'>';
        $dataContent .= '<input type=\'hidden\' name=\'id\' value=\''.$id.'\'>';
        $dataContent .= '<span class=\'input-group-btn\'>';
        $dataContent .= '<button type=\'submit\' name=\'onSpotEdit\' class=\'btn btn-default\'>OK</button>';
        $dataContent .= '</span>';
        $dataContent .= '</div>';
        return $dataContent;
    }

    public static function quickForm($action, $id, $type, $val)
    {
        $config = config();
        ?>
        <form action="<?=$config['url']?>/<?=$action?>" method="post">
            <input type="hidden" name="id" value="<?=$id?>">
            <div class="input-group input-group-sm">
                <input type="<?=$type?>" class="form-control" name="<?=$val?>" required="true">
                <span class="input-group-btn">
                    <button type="submit" class="btn btn-primary" name="quickFormSubmit">
                        <i class="fa fa-check"></i>
                    </button>
                </span>
            </div>
        </form>
        <?php
    }

    public static function escapeOutput($string)
    {
        $allowed = '<br><br /><a>';

        $output = $string;
        $output = strip_tags($output);
        $output = htmlspecialchars_decode($output);
        $output = preg_replace('!(((f|ht)tp(s)?://)[-a-zA-Z?-??-?()0-9@:%_+.~#?&;//=]+)!i', '<a href="$1">$1</a>', $output);

        return strip_tags($output, $allowed);
    }
}