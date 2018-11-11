<form action="<?=internalLink('question/changeCategory')?>" method="post" style="width:35%">
    <input type="hidden" name="id" value="<?php echo $data['question']->id; ?>">
    <div class="form-group">
        <select class="form-control" name="questionCategory">
            <option value="" disabled selected><?=output('QUESTION_CATEGORY')?></option>
            <option value="fash"><?=output('FASHION')?></option>
            <option value="web"><?=output('TECH/WEB')?></option>
            <option value="instr"><?=output('INSTRUCTION')?></option>
        </select>
    </div>
    <button type="submit" class="btn btn-default" name="change"><?=output('CHANGE')?></button>
</form>
<br/>