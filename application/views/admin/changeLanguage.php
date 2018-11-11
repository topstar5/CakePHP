<form action="<?=internalLink('question/changeLanguage')?>" method="post" style="width:35%">
    <input type="hidden" name="id" value="<?php echo $data['question']->id; ?>">
    <div class="form-group">
        <select class="form-control" name="questionLanguage">
            <option value="" disabled selected><?=output('QUESTION_LANGUAGE')?></option>
            <option value="en" ><?=output('ENGLISH')?></option>
            <option value="ge" ><?=output('GERMAN')?></option>
            <option value="fr" ><?=output('FRENCH')?></option>
            <option value="it" ><?=output('ITALIAN')?></option>
            <option value="gr" ><?=output('GREEK')?></option>
            <option value="sp" ><?=output('SPANISH')?></option>
            <option value="po" ><?=output('PORTUGUESE')?></option>
            <option value="ru" ><?=output('RUSSIAN')?></option>
            <option value="sw" ><?=output('SWEDISH')?></option>
            <option value="ch" ><?=output('CHINESE')?></option>
            <option value="ja" ><?=output('JAPANESE')?></option>
        </select>
    </div>
    <button type="submit" class="btn btn-default" name="change"><?=output('CHANGE')?></button>
</form>
<br/>