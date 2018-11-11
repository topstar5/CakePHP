<?php
    $user_id = $_SESSION['me'];
    $notification = BaseModel::fetch(array('inquiry_email', 'bitcoin_win_email', 'answer_inquiry_email'), 'users', array('id' => $user_id));
    foreach ($notification as $key => $email) {
        $inquiryEmail = $email['inquiry_email'];
        $rewardEmail = $email['bitcoin_win_email'];
        $answerEmail = $email['answer_inquiry_email'];
    }
?>

<form action="<?=internalLink('user/emailNotificationSettings')?>" method="post">
    <div class="form-group newPass">
        <label ><?=output('INQUIRY_EMAIL')?></label>&nbsp
        <?php if($inquiryEmail == 1) {
        ?>
            <input type="checkbox" name="inquiryEmail" value="inquiryEmail" checked><br />
        <?php
        } else {
        ?>
            <input type="checkbox" name="inquiryEmail" value="inquiryEmail"><br />
        <?php
        }
        ?>
        <label ><?=output('REWARD_EMAIL')?></label>&nbsp
        <?php if($rewardEmail == 1) {
        ?>
            <input type="checkbox" name="rewardEmail" value="rewardEmail" checked><br />
        <?php
        } else {
        ?>
            <input type="checkbox" name="rewardEmail" value="rewardEmail"><br />
        <?php
        }
        ?>
        <label ><?=output('ANSWER_EMAIL')?></label>&nbsp
        <?php if($answerEmail == 1) {
        ?>
            <input type="checkbox" name="answerEmail" value="answerEmail" checked><br />
        <?php
        } else {
        ?>
            <input type="checkbox" name="answerEmail" value="answerEmail"><br />
        <?php
        }
        ?>
    </div>
    <div class="form-group">
        <button type="submit" class="btn btn-default" name="emailNotificationUpdate"><?=output('UPDATE_EMAIL_NOTIFICATION')?></button>
    </div>
</form>

<!-- user/emailNotificationSettings -->