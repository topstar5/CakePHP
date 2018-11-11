<form action="<?=internalLink('question/sendEmail')?>" method="post">
    <input type="hidden" name="id" value="<?php echo $data['question']->id; ?>">
    <button type="submit" class="btn btn-default" name="sendEmail"><?=output('SEND_EMAIL')?></button>
</form>
<br>