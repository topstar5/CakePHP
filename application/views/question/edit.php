<?php

$question_id = $data['question']->id;
$question_title = $data['question']->text;
$question_descr = $data['question']->descr;
?>

<form action="<?=internalLink('question/editQuestion')?>" method="post" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?php echo $question_id; ?>">
    <div class="form-group">
        <textarea class="form-control" name="text" autofocus="on" required="true" rows="1"><?php echo $question_title; ?></textarea>
    </div>
    <div class="form-group">
        <textarea class="form-control" name="descr" rows="10"><?php echo $question_descr?></textarea>
    </div>
    <div class="col-md-4">
        <button type="submit" class="btn btn-default btn-block" style="margin-left: -14px;" name="questionSubmit">
            <?=output('EDIT')?>
        </button>
    </div>
</form>