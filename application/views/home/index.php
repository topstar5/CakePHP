<?php

$lang = strip_tags($_GET['lang']);

if(isset($data['lang'])) {
    $lang = $data['lang'];
}

if(isset($data['category'])) {
    $category = $data['category'];
}

if(isset($data['keyword'])) {
    $keyword = $data['keyword'];
}

if ($lang != 'ge' && $lang != 'fr' && $lang != 'it' && $lang != 'gr' && $lang != 'sp' && $lang != 'po' && $lang != 'ru' && $lang != 'sw' && $lang != 'ch' && $lang != 'ja') {
    $lang = 'en';
}

$thumbAttr = array('class' => 'top');
$x = 0;
$check = 0;

if (count($data['questions']) != 0) {

    foreach ($data['questions'] as $question) {
        if ($question->getLang() == $lang) {
            $check++;
            if ($check <= 10) {
                $x++;
                $user = $question->getUser();
                $question->isMine() ? $rowClass = 'questionRowMine' : $rowClass = '';

            ?>
            <div class="questionRow border-bottom <?=$rowClass?>">
                <div class="row">
                    <div class="col-md-2 col-sm-2 col-xs-3">
                        <a href="<?=internalLink('question/show/'.md5($question->id))?>">
                            <ul class="qThumb">
                                <li>
                                    <figure>
                                        <?=View::img($question->thumbnail(), $thumbAttr)?>
                                        <figcaption>
                                            <h2><?=output('REWARD')?></h2>
                                            <p><i class="fa fa-bitcoin"></i> <?=$question->reward?></p>
                                        </figcaption>
                                    </figure>
                                </li>
                            </ul>
                        </a>
                    </div>
                    <div class="col-md-10 col-sm-10 col-xs-9">
                        <a href="<?=internalLink('question/show/'.md5($question->id))?>">
                            <p class="h4"><?=$question->text?></p>
                        </a>
                        <ul class="list-inline small">
                            <li>
                                <?php
                                if ($question->closed) {
                                    ?> <i class="fa fa-lock"></i> <?php
                                } else {
                                    ?> <i class="fa fa-folder-open"></i> <?php
                                }
                                ?>
                            </li>
                            <li><?=output('ASKED_BY').' <b>'.$user->nickname.'</b>, '.date($data['config']['dateTimeFormat'], strtotime($question->created))?></li>
                            <li><a href="<?=internalLink('question/show/'.md5($question->id))?>"><?=count($question->answers).' '.output('ANSWERS')?></a></li><br>
                            <?php if ($question->category != null) {
                            ?>
                                <li>Category: <em><?php echo $question->getCategory(); ?></em></li>
                            <?php
                            }
                            ?>
                        </ul>
                    </div>
                </div>
            </div>
        <?php
            }
        }
    }
    if($x == 0) {
        ?> <h4 class="text-muted"><?=output('NO_QUESTIONS')?></h4> <?php
    }
} else {
    ?> <h4 class="text-muted"><?=output('NO_QUESTIONS')?></h4> <?php
}

$data['status'] == 'open' ? $status = 'open' : $status = 'closed';

?>

<nav class="qPager">
    <ul class="pager">
        <li class="previous <?= $data['page'] == 1 ? 'disabled' : null ?>">
        <?php 
            if ($data['keyword'] == null && $data['category'] == null) {
        ?>
            <a href="<?= internalLink('home/index/' . ($data['page'] - 1) . '/' . $status . '/' . $data['tag']) . '/?lang=' . $lang ?>">
                &larr; <?= output('NEWER') ?>
            </a>
        <?php } else if ($data['keyword'] != null && $data['category'] != null) {
        ?>
            <a href="<?=internalLink('home/search/'.($data['page'] - 1).'/'.$data['keyword'].'/'.$data['category'].'/'.$data['lang'])?>">
                &larr; <?= output('NEWER') ?>
            </a>
        <?php } else if ($data['keyword'] != null) {
        ?>
            <a href="<?=internalLink('home/search/'.($data['page'] - 1).'/'.$data['keyword'].'/0/'.$data['lang'])?>">
                &larr; <?= output('NEWER') ?>
            </a>
        <?php } else if ($data['category'] != null) {
            ?>
            <a href="<?=internalLink('home/search/'.($data['page'] - 1).'/0/'.$data['category'].'/'.$data['lang'])?>">
                &larr; <?= output('NEWER') ?>
            </a>
            <?php
            }
            ?>
        </li>
        <li class="next <?= $x != $data['config']['pageMax'] ? 'disabled' : null ?>">
        <?php 
            if ($data['keyword'] == null && $data['category'] == null) {
        ?>
            <a href="<?= internalLink('home/index/' . ($data['page'] + 1) . '/' . $status . '/' . $data['tag']) . '/?lang=' . $lang ?>">
                <?= output('OLDER') ?> &rarr;
            </a>
        <?php } else if ($data['keyword'] != null && $data['category'] != null) {
        ?>
            <a href="<?=internalLink('home/search/'.($data['page'] + 1).'/'.$data['keyword'].'/'.$data['category'].'/'.$data['lang'])?>">
                <?= output('OLDER') ?> &rarr;
            </a>
        <?php } else if ($data['keyword'] != null) {
        ?>
            <a href="<?=internalLink('home/search/'.($data['page'] + 1).'/'.$data['keyword'].'/0/'.$data['lang'])?>">
                <?= output('OLDER') ?> &rarr;
            </a>
        <?php } else if ($data['category'] != null) {
            ?>
            <a href="<?=internalLink('home/search/'.($data['page'] + 1).'/0/'.$data['category'].'/'.$data['lang'])?>">
                <?= output('OLDER') ?> &rarr;
            </a>
            <?php
            }
            ?>
        </li>
    </ul>
</nav>

<ul class="list-unstyled">
    <li>

        <?php

        if ($data['status'] == 'open') {
            ?> <a href="<?=internalLink('home/index/1/closed/'.$data['tag'].'/?lang='.$lang)?>"><?=output('CLOSED_QUESTIONS')?></a> <?php
        } else {
            ?> <a href="<?=internalLink('home/index/1/open/'.$data['tag'].'/?lang='.$lang)?>"><?=output('OPEN_QUESTIONS')?></a> <?php
        }

        ?>
    </li>

    <?php

    if ($_SESSION['loggedIn']) {
        ?>
        <li>
            <a href="<?= internalLink('home/index/1/mine'.'/?lang='.$lang) ?>"><?= output('MY_QUESTIONS') ?></a>
        </li>
        <?php
    }

    ?>

</ul>
