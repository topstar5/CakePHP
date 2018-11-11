<form action="<?=internalLink('question/search')?>" method="post">
    <br>
    <h4><?=output('SEARCH_QUESTIONS')?></h4>
    <div class="form-group">
        <input type="hidden" class="form-control" name="lang" value= <?= $lang; ?> >
        <input type="text" class="form-control" name="keyword" placeholder="<?=output('KEYWORD')?>">
    </div>
    <div class="form-group">
        <select class="form-control" style="width: 100%;" name="category">
            <option value="" disabled selected><?=output('QUESTION_CATEGORY')?></option>
            <option value="fash"><?=output('FASHION')?></option>
            <option value="web"><?=output('TECH/WEB')?></option>
            <option value="instr"><?=output('INSTRUCTION')?></option>
        </select>
    </div>
    <button type="submit" class="btn btn-default" name="change"><?=output('SEARCH_SUBMIT')?></button>
</form>
<br>