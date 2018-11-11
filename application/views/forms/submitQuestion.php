<script type="application/javascript" src="<?=Application::getConfig()['url']?>/js/jquery.dd.min.js"></script>
<script language="javascript">
$(document).ready(function(e) {
try {
$("body select").msDropDown();
} catch(e) {
alert(e.message);
}
});
</script>
<form action="<?=internalLink('question/submit')?>" method="post" enctype="multipart/form-data">
    <h4><?=output('SUBMIT_QUESTION')?></h4>
    <div class="form-group">
        <select class="form-control" id="sel1" name="questionLanguage">
            <option value="" disabled selected><?=output('QUESTION_LANGUAGE')?></option>
            <option value="en" data-image="<?=Application::getConfig()['url']?>/img/flags/shiny/32/United-States.png"><?=output('ENGLISH')?></option>
            <option value="ge" data-image="<?=Application::getConfig()['url']?>/img/flags/shiny/32/Germany.png"><?=output('GERMAN')?></option>
            <option value="fr" data-image="<?=Application::getConfig()['url']?>/img/flags/shiny/32/France.png"><?=output('FRENCH')?></option>
            <option value="it" data-image="<?=Application::getConfig()['url']?>/img/flags/shiny/32/Italy.png"><?=output('ITALIAN')?></option>
            <option value="gr" data-image="<?=Application::getConfig()['url']?>/img/flags/shiny/32/Greece.png"><?=output('GREEK')?></option>
            <option value="sp" data-image="<?=Application::getConfig()['url']?>/img/flags/shiny/32/Spain.png"><?=output('SPANISH')?></option>
            <option value="po" data-image="<?=Application::getConfig()['url']?>/img/flags/shiny/32/Portugal.png"><?=output('PORTUGUESE')?></option>
            <option value="ru" data-image="<?=Application::getConfig()['url']?>/img/flags/shiny/32/Russia.png"><?=output('RUSSIAN')?></option>
            <option value="sw" data-image="<?=Application::getConfig()['url']?>/img/flags/shiny/32/Sweden.png"><?=output('SWEDISH')?></option>
            <option value="ch" data-image="<?=Application::getConfig()['url']?>/img/flags/shiny/32/China.png"><?=output('CHINESE')?></option>
            <option value="ja" data-image="<?=Application::getConfig()['url']?>/img/flags/shiny/32/Japan.png"><?=output('JAPANESE')?></option>
        </select>
    </div>
    <div class="form-group">
        <select class="form-control" id="sel2" name="questionCategory">
            <option value="" disabled selected><?=output('QUESTION_CATEGORY')?></option>
            <option value="fash"><?=output('FASHION')?></option>
            <option value="web"><?=output('TECH/WEB')?></option>
            <option value="instr"><?=output('INSTRUCTION')?></option>
        </select>
    </div>
    <div class="form-group">
        <textarea class="form-control" name="questionText" placeholder="<?=output('QUESTION')?>" autofocus="on" required="true" rows="1"></textarea>
    </div>
    <div class="form-group">
        <textarea class="form-control" name="questionDescr" placeholder="<?=output('DESCRIPTION')?> (<?=output('OPTIONAL')?>)" rows="2"></textarea>
    </div>
    <div class="form-group">
        <textarea class="form-control" name="questionTags" placeholder="<?=output('TAGS')?> (<?=output('OPTIONAL')?>)" rows="1"></textarea>
        <div class="help-block small">
            <p><?=output('SEPARATE_BY_COMMA')?></p>
        </div>
    </div>
    <div class="form-group">
        <input type="file" name="questionFile" accept="image/*">
    </div>
    <div class="form-group row">
        <div class="col-md-8">
            <div class="input-group">
                <input type="text" class="form-control" name="questionReward" placeholder="<?=output('REWARD')?>" required="true">
                <span class="input-group-addon">BTC</span>
            </div>
            <div class="help-block">
                <ul class="list-inline small">
                    <li><?=output('MAX')?>: <span class="walletBalance"><?=$data['wallet']['balance']?></span> BTC</li>
                    <li><?=output('MIN')?>: <?=$data['config']['minReward']?> BTC</li>
                </ul>
            </div>
        </div>
        <div class="col-md-4">
            <button type="submit" class="btn btn-default btn-block" name="questionSubmit">
                <?=output('SUBMIT')?>
            </button>
        </div>
    </div>
</form>