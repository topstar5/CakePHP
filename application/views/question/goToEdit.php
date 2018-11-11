<form action="<?=internalLink('question/edit')?>" method="post">
    <input type="hidden" name="id" value="<?php echo $data['question']->id; ?>">
    <button type="submit" class="btn btn-default" name="sendEmail"><?=output('EDIT')?></button>
</form>