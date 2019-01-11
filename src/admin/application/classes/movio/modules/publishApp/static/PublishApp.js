$(function(){
    var progressPart, progress, steps, stepPos, stepResult, $button;

    function openExportDialog() {
        window.onbeforeunload = exitWarning;
        $( "#progress_bar" ).modal({ overlayCss: {background: "#000"}, overlayClose: false, closeHTML:'' });
    }

    function closeExportDialog() {
        window.onbeforeunload = null;
        $.modal.close();
        resetProgressBar();
    }

    function exitWarning(e) {
        var msg = 'Attenzione, uscendo da questa pagina verra\' interrotto il processo in corso!';
        e = e || window.event;
        // For IE and Firefox prior to version 4
        if (e) {
          e.returnValue = msg;
        }
        // For Safari
        return msg;
    };

    function resetProgressBar() {
        jQuery.fx.off = true;
        $('#progress_bar .ui-progress').width("0%");
        $('#progress_bar .ui-label').hide();
    };

    function execStep() {
        if ( stepPos >= steps.length )
        {
            $('#progress_bar .ui-progress').animateProgress(100);
            setTimeout(function(){
                alert( 'Esportazione completata' );
                $button.removeAttr("disabled");
                closeExportDialog();
                if ( stepResult.status )
                {
                    location.href = stepResult.result;
                }
            }, 1000);
            return;
        }

        progress += progressPart;
        $('#progress_bar .ui-progress').animateProgress( progress );

        // per ogni azione esegue una richiesta ajax
        jQuery.ajax( {
            url: Glizy.ajaxUrl+steps[ stepPos ].action,
            data: steps[ stepPos ].params,
            dataType: "json",
            success: function( data ){
                if (data.result) {
                    $("#graph").hide();

                    $.each(data.result, function( index, value ) {
                        $("#graph").html(value['code']);

                        $.ajax({
                            url: Glizy.ajaxUrl+"saveSVG",
                            async: false,
                            dataType: 'json',
                            type: 'POST',
                            data: {
                                languageCode: value['languageCode'],
                                id: value['id'],
                                svg: $('#graph_div').html(),
                                type: value['type']
                            },
                            success: function( data ) {
                            }
                        });
                    });
                }
                stepResult = data;
                stepPos++;
                execStep();
            } } );
    };

    function getTreeData() {
        var menuIdArray = [];

        $("#js-glizycmsSiteTree").find(".jstree-undetermined").each(function(i, element){
        	menuIdArray.push($(element).attr("id"));
        });

        $("#js-glizycmsSiteTree").find(".jstree-checked").each(function(i, element){
        	menuIdArray.push($(element).attr("id"));
        });

        return menuIdArray;
    };

    $('input.js-publish-app').click(function(e){
        $button = $(this);
        $button.attr("disabled", "disabled");

        e.preventDefault();

        var menuIdArray = getTreeData();

        if (menuIdArray.length == 0) {
            alert( 'Selezionare almeno una pagina da esportare.' );
            $button.removeAttr("disabled");
            return;
        }

        $.ajax({
            url: Glizy.ajaxUrl+"getSteps",
            dataType: 'json',
            data: {
                languages: $('#languages').val(),
                menuIdArray: menuIdArray,
                title: $('#title').val(),
                subtitle: $('#subtitle').val(),
                creditPageId: $('#creditPageId').val(),
                isExhibitionActive: $('#isExhibitionActive').attr('checked'),
            },
            success: function( data ) {
                if ( data.status )
                {
                    if (data.result.length == 0) {
                        alert( 'Non ci sono dati da esportare' );
                    } else {
                        openExportDialog();
                        progressPart = 100 / data.result.length;
                        progress = 0;
                        stepPos = 0;
                        steps = data.result;
                        execStep();
                    }
                }
                else
                {
                    alert( 'Si è verificato un errore' );
                    $button.removeAttr("disabled");
                }
            }
        });
    })
});