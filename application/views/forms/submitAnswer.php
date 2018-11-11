<form action="<?=internalLink('question/answer/'.md5($data['question']->id))?>" method="post">
    <div class="form-group">
        <textarea class="form-control" name="answerText" placeholder="<?=output('ANSWER')?>" required="true"></textarea>
    </div>
    <div class="form-group">
        <button type="submit" class="btn btn-default" name="answerSubmit">
            <?=output('SUBMIT_ANSWER')?>
        </button>
    </div>
</form>