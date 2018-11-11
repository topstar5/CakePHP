<div role="tabpanel">
    <ul class="nav nav-tabs" role="tablist">
        <li class="active"><a href="#signIn" aria-controls="signIn" role="tab" data-toggle="tab"><?=output('SIGN_IN')?></a></li>
        <li><a href="#signUp" aria-controls="signUp" role="tab" data-toggle="tab"><?=output('SIGN_UP')?></a></li>
    </ul>
    <br>
    <div class="tab-content">
        <form id="signIn" class="tab-pane fade in active" action="<?=internalLink('user/login')?>" method="post" role="tabpanel">
            <div class="form-group">
                <a href="<?=$data['fbLoginUrl']?>" class="btn btn-facebook btn-block">
                    <i class="fa fa-facebook"></i> <?=output('SIGN_IN_WITH_FB')?>
                </a>
            </div>
            <div class="orHeading">- <?=output('OR')?> -</div>
            <div class="form-group">
                <input type="email" class="form-control" name="loginEmail" placeholder="<?=output('EMAIL_ADDR')?>" required="true">
            </div>
            <div class="form-group">
                <input type="password" class="form-control" name="loginPassword" placeholder="<?=output('PASSW')?>" required="true">
            </div>
            <div class="form-group row">
                <div class="col-md-6">
                    <button type="submit" class="btn btn-default" name="loginSubmit"><?=output('SIGN_IN')?></button>
                </div>
                <div class="col-md-6">
                    <a href="#resetPassw" aria-controls="resetPassw" role="tab" data-toggle="tab"><?=output('FORGOT_PASSW')?></a>
                </div>
            </div>
        </form>
        <form id="resetPassw" class="tab-pane fade" action="<?=internalLink('user/resetPass')?>" method="post">
            <div class="form-group">
                <div class="input-group input-group-lg">
                    <input type="email" class="form-control" name="resetPassEmail" placeholder="<?=output('EMAIL_ADDR')?>" required="true">
            <span class="input-group-btn">
                <button type="submit" class="btn btn-default" name="resetPassSubmit"><?=output('CONFIRM')?></button>
            </span>
                </div>
            </div>
        </form>
        <form id="signUp" class="tab-pane fade" action="<?=internalLink('user/register')?>" method="post" role="tabpanel" onsubmit="return validateRegisterForm()">
            <div class="form-group">
                <a href="<?=$data['fbLoginUrl']?>" class="btn btn-facebook btn-block">
                    <i class="fa fa-facebook"></i> <?=output('SIGN_UP_WITH_FB')?>
                </a>
            </div>
            <div class="orHeading">- <?=output('OR')?> -</div>
            <div class="form-group">
                <input type="email" class="form-control" name="registerEmail" placeholder="<?=output('EMAIL_ADDR')?>" required="true">
            </div>
            <div class="form-group">
                <input type="text" class="form-control" name="registerNickname" placeholder="<?=output('NICKNAME')?>" required="true">
            </div>
            <div class="form-group password">
                <input type="password" class="form-control" name="registerPassword" placeholder="<?=output('PASSW')?>" required="true">
            </div>
            <div class="form-group password">
                <input type="password" class="form-control" name="registerPasswordConfirm" placeholder="<?=output('PASSW_CONFIRM')?>" required="true">
            </div>
            <p id="registerPasswordAlert" class="text-danger hidden">
                <?=output('PASSW_DONT_MATCH')?>
            </p>
            <div class="form-group">
                <a href="#">
                    <img class="img-responsive img-thumbnail" id="captcha" width="100%"
                         onclick="document.getElementById('captcha').src = '<?=internalLink('plugins/securimage/securimage_show.php?')?>' + Math.random(); return false"
                         src="<?=internalLink('plugins/securimage/securimage_show.php')?>" alt="CAPTCHA Image">
                </a>
            </div>
            <div class="form-group">
                <input type="text" class="form-control" name="registerCaptcha" placeholder="<?=output('CAPTCHA')?>" required="true">
            </div>
            <div class="form-group row">
                <div class="col-md-6">
                    <button type="submit" class="btn btn-default" name="registerSubmit"><?=output('SIGN_UP')?></button>
                </div>
            </div>
        </form>
    </div>    
</div>
  <div class="video-container">
        <iframe width="400" height="315" src="https://www.youtube.com/embed/VZKd2zhoE8E" frameborder="50" 
    allowfullscreen></iframe>
 </div>
<script>
    $('.form-control').addClass('input-sm');
    $('.btn-default').addClass('btn-sm');

    function validateRegisterForm()
    {
        var pass = $('[name=registerPassword]').val();
        var passConfirm = $('[name=registerPasswordConfirm]').val();

        if (pass == passConfirm) {
            $('.password').removeClass('has-error');
            $('#registerPasswordAlert').addClass('hidden');
            return true;
        } else {
            $('.password').addClass('has-error');
            $('#registerPasswordAlert').removeClass('hidden');
            return false;
        }
    }
</script>
<style>
.video-container{
    margin-top: 100px;
}

</style>
