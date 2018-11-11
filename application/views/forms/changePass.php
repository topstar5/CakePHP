<form action="<?=internalLink('user/changePass')?>" method="post" onsubmit="return validate()">
    <?php
    if (!$data['user']->hasNoPassword()) {
        ?>
        <div class="form-group">
            <label><?= output('OLD_PASSW') ?></label>
            <input type="password" class="form-control" name="changePassOldPass" required="true" autocomplete="off">
        </div>
    <?php
    }
    ?>
    <div class="form-group newPass">
        <label><?=output('NEW_PASSW')?></label>
        <input type="password" class="form-control" name="changePassNewPass" required="true" autocomplete="off">
    </div>
    <div class="form-group newPass">
        <label><?=output('NEW_PASSW_CONFIRM')?></label>
        <input type="password" class="form-control" name="changePassNewPassConfirm" required="true" autocomplete="off">
    </div>
    <p id="changePassAlert" class="text-danger collapse"><?=output('pass-dont-match')?></p>
    <div class="form-group">
        <button type="submit" class="btn btn-default" name="changePassSubmit"><?=output('CHANGE_PASSW')?></button>
    </div>
</form>

<script>

    function validate()
    {
        var pass = $('[name=changePassNewPass]').val();
        var passConfirm = $('[name=changePassNewPassConfirm]').val();

        if (pass == passConfirm) {
            $('.newPass').removeClass('has-error');
            $('#changePassAlert').slideUp();
            return true;
        } else {
            $('.newPass').addClass('has-error');
            $('#changePassAlert').slideDown();
            return false;
        }
    }

</script>