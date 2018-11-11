<form action="<?=internalLink('user/withdraw')?>" method="post">
    <h4><?=output('WITHDRAW_BTC')?></h4>
    <div class="form-group">
        <input type="text" class="form-control" name="withdrawAddress"  maxlength="34" required="true" placeholder="<?=output('BTC_ADDR')?>">
    </div>
    <div class="row">
        <div class="form-group col-md-8">
            <div class="input-group">
                <input type="text" class="form-control" name="withdrawAmount" required="true" placeholder="<?=output('AMOUNT')?>">
                <span class="input-group-addon">BTC</span>
            </div>
        </div>
        <div class="form-group col-md-4">
            <button type="submit" class="btn btn-default btn-block" name="withdrawSubmit">
                <?=output('WITHDRAW')?>
            </button>
        </div>
    </div>
</form>