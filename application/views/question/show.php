<?php 
$data['user']->admin;
$data['question']->email_sent;
$question_language = $data['question']->question_lang;

switch ($question_language) {
    case 'en':
        $img_path = "/img/flags/shiny/32/United-States.png";
        $alt = "English";
        break;

    case 'ge':
        $img_path = "/img/flags/shiny/32/Germany.png";
        $alt = "German";
        break;

    case 'fr':
        $img_path = "/img/flags/shiny/32/France.png";
        $alt = "French";
        break;

    case 'it':
        $img_path = "/img/flags/shiny/32/Italy.png";
        $alt = "Italian";
        break;

    case 'gr':
        $img_path = "/img/flags/shiny/32/Greece.png";
        $alt = "Greek";
        break;

    case 'sp':
        $img_path = "/img/flags/shiny/32/Spain.png";
        $alt = "Spanish";
        break;

    case 'po':
        $img_path = "/img/flags/shiny/32/Portugal.png";
        $alt = "Portuguese";
        break;

    case 'ru':
        $img_path = "/img/flags/shiny/32/Russia.png";
        $alt = "Russian";
        break;

    case 'sw':
        $img_path = "/img/flags/shiny/32/Sweden.png";
        $alt = "Swedish";
        break;

    case 'ch':
        $img_path = "/img/flags/shiny/32/China.png";
        $alt = "Chinese";
        break;

    case 'ja':
        $img_path = "/img/flags/shiny/32/Japan.png";
        $alt = "Japanese";
        break;

        default:
        $img_path = "/img/flags/shiny/32/United-States.png";
        $alt = "English";
        break;
}

?>
<ul class="list-inline text-muted qLine">
    <li><b><?=$data['question']->user->nickname?></b></li>
    <li><?=date($data['config']['dateTimeFormat'], strtotime($data['question']->created))?></li>
    <li><img src="<?=Application::getConfig()['url']?>/<?=$img_path?>" alt="<?=$alt?>"></li>
    <li>
        <div class="fb-like" data-href="<?=internalLink('question/show/'.md5($data['question']->id))?>" data-layout="button_count" data-action="like" data-show-faces="false" data-share="true"></div>
    </li>
    <li>
        <a class="twitter-share-button" data-text="<?=View::shortStr($data['question']->text, 120)?>" data-lang="<?=lang()?>" href="https://twitter.com/share">
            Tweet
        </a>
    </li>
    <li class="label label-success pull-right" style="font-size: medium"><?=$data['question']->reward?> BTC</li>
</ul>

<?php

if ($data['question']->category != null) {
?>
    <p>Category: <em><?php echo $data['question']->getCategory(); ?></em></p>
<?php
}

if (count($data['question']->tags) != 0) {
    ?>
    <ul class="list-inline text-muted qLine">
        <?php

        foreach ($data['question']->tags as $tag) {
            ?>
            <li>
                <a class="label label-default" href="<?=internalLink('home/index/1/all/'.$tag)?>">
                    <i class="fa fa-tag"></i> <?=$tag?>
                </a>
            </li>
            <?php
        }

        ?>
    </ul>
    <?php
}
if ( $data['user']->admin == 1) {
    ?>
    <div class="jumbotron">
        <h4><u>Admin panel:</u></h4><br>
        <span style="margin-bottom: 2%">
        <?php
        if ( $data['user']->admin == 1) {
            require_once '../application/views/admin/changeLanguage.php';
        }
        ?>
        </span>
        <span style="margin-bottom: 2%">
        <?php
        if ( $data['user']->admin == 1) {
            require_once '../application/views/admin/changeCategory.php';
        }
        ?>
        </span>
        <span style="margin-bottom: 2%">
        <?php
        if ( $data['user']->admin == 1 && $data['question']->email_sent == 0) {
            require_once '../application/views/admin/emailSending.php';
        }

        ?>
        </span>
    </div>
<?php
}
?>

<span>
<?php
if ( $data['user']->id == $data['question']->user_id && $data['question']->id > 303 && $data['question']->closed == 0) {
    require_once '../application/views/question/goToEdit.php';
}

?>
</span>

<br>
<p class="h4"><?=$data['question']->text?></p>

<?php

if (!empty($data['question']->descr)) {
    
    if($data['question']->id < 304) {
    ?> <p><?=View::escapeOutput($data['question']->descr)?></p> <?php
    } else {
    ?> <p><?=nl2br($data['question']->descr);?></p>
    <?php
    }
}

if (!empty($data['question']->file) && file_exists('uploads/questions/'.$data['question']->file)) {
    ?>
    <br>
    <p>
        <img class="img-responsive img-thumbnail" src="<?=internalLink('uploads/questions/'.$data['question']->file)?>">
    </p>
    <br>
<?php
}

?>
<hr>
<?php

if (!$data['question']->closed) {
    if ($_SESSION['loggedIn']) {
        if ($data['question']->user_id == $data['user']->id) {
            echo '<p>'.output('CANT_ANSWER_OWN_QUESTION').'</p>';
        } else if (!$data['question']->hasBeenAnsweredBy($data['user'])) {
            require_once '../application/views/forms/submitAnswer.php';
        } else {
            echo '<p>'.output('ALREADY_ANSWERED').'</p>';
        }
    } else {
        echo '<p>'.output('SIGN_IN_TO_ANSWER').'</p>';
    }
} else {
    echo '<p>'.output('QUESTION_CLOSED').'</p>';
}

if (count($data['question']->answers) != 0) {
    ?>
    <br>
    <h4><?=count($data['question']->answers)?> <?=output('ANSWERS')?></h4>
    <?php

    foreach ($data['question']->answers as $answer) {
        ?>
        <div class="well well-sm">
            <ul class="list-inline text-muted">
                <li><b><?=$answer->user->nickname?></b></li>
                <li><?=date($data['config']['dateTimeFormat'], strtotime($answer->created))?></li>
                <?php

                if (!$data['question']->closed && $data['question']->isMine()) {
                    ?>
                    <li>
                        <a class="btn btn-success btn-sm" href="<?=internalLink('question/markAnswer/'.md5($data['question']->id)).'/'.md5($answer->id)?>">
                            <?=output('MARK_AS_CORRECT')?>
                        </a>
                    </li>
                    <?php
                } else if ($answer->correct) {
                    ?> <li class="label label-success"><?=output('CORRECT')?></li> <?php
                }

                ?>
            </ul>
            <p><?=View::escapeOutput($answer->text)?></p>
            <div class="text-muted">
                <?php

                if (count($answer->comments) != 0) {
                    ?>
                    <br>
                    <p><?=output('COMMENTS')?></p>
                    <ul>
                    <?php

                    foreach ($answer->comments as $comment) {
                        ?>
                        <li><b><?=$comment->user->nickname?></b>: <?=$comment->text?></li>
                        <?php
                    }

                    ?>
                    </ul>
                    <?php
                }

                if ($answer->allowedToComment($data['user'])) {
                    ?>
                    <form action="<?=internalLink('question/commentAnswer/'.md5($answer->id))?>" method="post">
                        <br>
                        <div class="form-group">
                            <div class="input-group">
                                <textarea type="text" class="form-control" name="commentText" required="true" placeholder="<?=output('YOUR_COMMENT')?>"></textarea>
                            <span class="input-group-btn">
                                <button type="submit" class="btn btn-default"><?=output('COMMENT')?></button>
                            </span>
                            </div>
                        </div>
                    </form>
                <?php
                }

                ?>
            </div>
        </div>
        <?php
    }
}

?>

<script>
    window.twttr=(function(d,s,id){
        var js,fjs=d.getElementsByTagName(s)[0],t=window.twttr||{};
        if(d.getElementById(id))return;js=d.createElement(s);js.id=id;js.src="https://platform.twitter.com/widgets.js";

        fjs.parentNode.insertBefore(js,fjs);t._e=[];t.ready=function(f){
            t._e.push(f);
        };

        return t;
    }(document,"script","twitter-wjs"));
</script>