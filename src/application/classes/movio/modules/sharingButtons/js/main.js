GlizyApp.pages[ 'movio.modules.sharingButtons.views.Admin' ] = function( state, routing ) {
    $(function(){
        if ('index'==state) {
            var setList = function(){
                $("#enabledButton").val('');
                $('ul.js-sharingEnabled li').each(function(i)
                {
                    if(!$('#enabledButton').val()){
                        $("#enabledButton").val($(this).text());
                    }else{
                        $("#enabledButton").val($("#enabledButton").val() + "," + $(this).text());
                    }
                });
            }

            $( "ul.js-sharingDisabled" ).sortable({
                connectWith: "ul"
            });

            $( "ul.js-sharingEnabled" ).sortable({
                connectWith: "ul"
            });

            $( "ul.js-sharingDisabled, ul.js-sharingEnabled" ).disableSelection();

            $("#shareButtonDim").blur();

           setList();

           $( "ul.js-sharingEnabled" ).on( "sortchange sortupdate", function( event, ui ) {
                setList();
           });
        }
    });
}

