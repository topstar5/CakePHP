<script>

    function isNumberKey(evt){
        var charCode = (evt.which) ? evt.which : event.keyCode;

        if(charCode != 46 && charCode > 31 && (charCode < 48 || charCode > 57)){
            return false;
        }else{
            return true;
        }
    }

    $('.copyToClipboard').html('<i class="fa fa-copy"></i>');

    $('.copyToClipboard').attr({
        "data-toggle": "tooltip",
        "title": "<?=output('COPY_TO_CLIPBOARD')?>",
        "data-placement": "bottom"
    });

    $('.copyToClipboard').click(function() {
        $(this).html('<i class="fa fa-check"></i>');
    });

    var clientText = new ZeroClipboard($(".copyToClipboard"), {
        moviePath: "../public/plugins/zeroClipboard/ZeroClipboard.swf",
        debug: false
    } );

    clientText.on( "load", function(clientText) {
        clientText.on( "complete", function(clientText, args) {
            clientText.setText( args.text );
        });
    });

    $('[data-toggle="tooltip"]').tooltip();
    $('[data-toggle="popover"]').popover();

</script>