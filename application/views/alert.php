<?php

if (isset($data['alert']) && !empty($data['alert']['text'])) {
    ?>
    <div class="alert <?=$data['alert']['class']?>" id="divAlert">
        <div class="container">
            <span id="divAlertText"><?=$data['alert']['text']?></span>
        </div>
    </div>
    <?php
}

?>

<noscript>
    <div class="alert alert-danger">
        <div class="container">
            <h2>Please enable Javascript in your browser.</h2>
        </div>
    </div>
</noscript>