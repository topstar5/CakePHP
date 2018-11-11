<?php

if (!isset($_SESSION['loggedIn']) || !$_SESSION['loggedIn']) {
    require_once '../application/views/forms/login.php';
} else {
    ?>
    <button type="button" class="btn btn-info" data-toggle="collapse" data-target="#search"><?=output('SEARCH')?></button><br>
    <div id="search" class="collapse">
        <?php require_once '../application/views/forms/search.php'; ?>
    </div>
    <br>
    <ul class="list-unstyled">
        <li>
            <?=output('LOGGED_IN_AS')?> <a href="<?=internalLink('user')?>"><b><?=$data['user']->nickname?></b></a>
            <a href="<?=internalLink('user/logout')?>"><small>(<?=output('SIGN_OUT')?>)</small></a>
        </li>
        <li>
            <?=output('BALANCE')?>: <b><span class="walletBalance"><?=$data['wallet']['balance']?></span> BTC</b>
            <?php
            if ($data['wallet']['balance'] > 0) {
                ?> <a href="#withdraw" data-toggle="collapse"><small>(<?=output('WITHDRAW')?>)</small></a> <?php
            }
            ?>
        </li>
        <li>
            <code data-toggle="tooltip" title="<?=output('BTC_ADDR')?>"><?=$data['wallet']['address']?></code>
            <button class="btn btn-link small copyToClipboard" data-clipboard-text="<?=$data['wallet']['address']?>"></button>
            <a href="#qrCode" class="btn btn-link small" data-toggle="collapse"><i class="fa fa-qrcode"></i></a>
        </li>
        <br>
        <li id="qrCode" class="collapse text-center">
            <span class="img-thumbnail">
                <?=View::QRcode('bitcoin://'.$data['wallet']['address'].'?label='.$data['config']['name'], 6)?>
            </span>
        </li>
    </ul>

    <script>
        $('.walletBalance').load('<?=internalLink('ajax/walletBalance')?>');
    </script>
    <?php

    if ($data['wallet']['balance'] > 0) {
        require_once '../application/views/forms/submitQuestion.php';
        ?>
        <br>
        <div id="withdraw" class="collapse">
            <?php require_once '../application/views/forms/withdraw.php'; ?>
        </div>
        <?php
    } else {
        ?> <p><?=output('FUND_ACCOUNT_EXPL')?></p> <?php
    }
}

?>