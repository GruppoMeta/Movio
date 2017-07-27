jQuery.GlizyRegisterType('europeanaRelatedContents', {

    __construct: function () {
        var self = $(this).data('formEdit');
        self.val = $(this).val();
        var id = $(this).attr('id');
        var searchFields = $(this).data('search_fields').split(',');
        var searchFieldsLabels = $(this).data('search_fields_labels').split(',');
        var checkList = [];
        var params = {};
        var templateData = {};
        var arrParams = [];
        var savedImage = [];
        var searchMode = '';
        var defaultMaxResult =  $(this).data('max_result');
        var isResult = true;
        var totalImage = true;
        self.element = $(this);
        glizyOpt = $(this).data('glizyOpt');
        preview = $("#preview").val();


        var language = { 'it'  : 'Italiano',
                         'de'  : 'Deutsch',
                         'mul' : 'Multilingue',
                         'es'  : 'Español',
                         'nl'  : 'Nederlands',
                         'en'  : 'English',
                         'fr'  : 'Français',
                         'ro'  : 'Română',
                         'pl'  : 'Polski',
                         'lv'  : 'Latviešu',
                         'pt'  : 'Português',
                         'cs'  : 'Čeština',
                         'sv'  : 'Svenska',
                         'hu'  : 'Magyar',
                         'nb'  : 'Norsk',
                         'ca'  : 'Català',
                         'lt'  : 'Lietuvių',
                         'da'  : 'Dansk',
                         'bg'  : 'Български',
                         'fi'  : 'Suomi',
                         'et'  : 'Eesti',
                         'sl'  : 'Slovenščina',
                         'sk'  : 'Slovenský',
                         'srp' : 'Serbian',
                         'el'  : 'Ελληνικά',
                         'swe' : 'Swedish',
                         'ru'  : 'Русский',
                         'hr'  : 'Hrvatski',
                         'tr'  : 'Turkish' };

        var mediaType = { 'TEXT'  : '{i18n:text}',
                          'IMAGE' : '{i18n:image}',
                          'VIDEO' : '{i18n:video}',
                          'SOUND' : '{i18n:sound}',
                          '3D'    : '{i18n:3D}' };

        var templateImageList = '<div id="imgList" class="row_fluid span10 europeana-group">' +
                                  '<ul id="sortable" class="thumbnails">'+
                                    '<% _.each( rc.savedImage, function(image) { %>' +
                                        '<li>' +
                                            '<div class="thumbnail">' +
                                                '<div class="js-imageRemove" >' +
                                                    '<i id="<%- image.id %>" class="icon-remove icon-white pull-right"></i>' +
                                                '</div>' +
                                                '<div class="imgPreview">' +
                                                    '<div>' +
                                                        '<a title="<%- image.title %>" href="<%- image.href %>" target="_blank">' +
                                                            '<img title="<%- image.title %>" src="<%- image.src %>">' +
                                                        '</a>' +
                                                    '</div>' +
                                                    '<div>' +
                                                        '<span><%- image.title.length >= 30 ? image.title.substring(0, 30) + \'...\' : image.title %></span>' +
                                                    '</div>' +
                                                '</div>' +
                                            '</div>'+
                                        '</li>' +
                                    '<% });%>' +
                                  '</ul>' +
                                '</div>';

        var htmlTemplate =
               '<div class="row-fluid span10 europeana-group firstDiv" >' +
                '<div class="span4">'+
                    '<div class="span6">' +
                        '<label for="searchMode">' +
                            '{i18n:Search mode}' +
                        '</label>' +
                        '<select id="searchMode">' +
                            '<option value="checkBoxEnable">{i18n:Select images}</option>' +
                            '<option value="checkBoxDisable">{i18n:All images}</option>' +
                        '</select>' +
                    '</div>' +
                    '<div class="span6 maxResDiv" id="maxImgResultDiv">' +
                            '<label for="maxImgResult">' +
                                '{i18n:Results number}' +
                            '</label>' +
                        '<input id="maxImgResult" type="number" value="<%- rc.maxResult %>" min="1" max="100"/>' +
                    '</div>' +
                   '</div>' +
                    '<% var i = 2; %>' +
                    '<% _.each( rc.fields, function(field ){ '+
                        'var spanX = (i==2) ? "span8" : "span4";' +
                            'if(i%3 == 0) {%>' +
                              '</div>' +
                              '<div class="row-fluid span10 europeana-group" >' +
                            '<%}%>' +
                    '<div class="row-fluid <%- spanX %>">' +
                        '<label for = "<%- rc.id %>_input_<%- field.id %>" >' +
                            '<%- field.label %>' +
                        '</label>' +
                        '<% if(field.id === "TYPE" || field.id === "LANGUAGE") {%>' +
                           '<select id="<%- rc.id %>_input_<%- field.id %>">' +
                                '<option value="">{i18n:Make a choice} ...</option>' +
                                '<% var arrayOption = field.id === "TYPE" ? rc.TYPE : rc.LANGUAGE;%>' +
                                 '<% _.each( arrayOption, function(option, key) { '+
                                    'var selected = field.precompiled === key ? \'selected="selected"\' : ""; %>' +
                                   '<option value="<%- key %>" <%- selected %>><%- option %></option>' +
                                '<%});%>' +
                            '</select>' +
                        '<%} else {%>' +
                            '<input <% if(i==2){%> class="titleInput" <%}%> type="text" id = "<%- rc.id %>_input_<%- field.id %>" value="<%- field.precompiled %>"/>' +
                        '<%}%>' +
                    '</div>' +
                '<% i++;});%>' +
                '</div>' +
                '<div class="row-fluid span10 europeana-group div-btn">' +
                    '<div class="pull-right">' +
                        '<input id="<%- rc.id %>_previewButton" type="button" class="btn" value="{i18n:Preview}">' +
                    '</div>' +
                '</div>' +
                templateImageList;


        self.init = function()
        {
            $("label[for="+ id + "]").css('cursor', 'default');
            self.element.parent().hide();
            if (self.val) {
                var values = $.parseJSON(self.val);
                $.each(values, function(key, value) {
                    arrParams[key] = value;
                });
            }

            var fields = [];
            $(searchFields).each(function (i, searchField) {
                var obj = {
                    id: searchField,
                    label: searchFieldsLabels[i],
                    precompiled : arrParams[searchField]
                };
                fields.push(obj);
            });

            self.initImageParams();

            searchMode =  arrParams['imgCheckBox'];
            var maxResult = $.isNumeric(arrParams['maxResult']) ? arrParams['maxResult'] : defaultMaxResult;

            templateData = { 'id' : id,
                             'fields': fields,
                             'imgList': checkList,
                             'savedImage' : savedImage,
                             'maxResult' : maxResult,
                             'TYPE'      : mediaType,
                             'LANGUAGE'  : language
                            };

            self.render(self.element.parent(), templateData, htmlTemplate, true);
            self.setSearchMode();
            self.activatePreview( self.checkFormIsFilled());

            $('#'+id+'_previewButton').on('click', function() {
                parms = self.insertSearchParam(self.element);
                self.sendRequest( parms );
            });

            $('#searchMode').on('change', function() {
                searchMode = $( "#searchMode option:selected" ).val();
                self.setSearchMode()
            });

            $('#searchMode').trigger('change');

            $(document).on('click', '.js-imageRemove', function() {
                imgId = $(this).find('i').attr('id');
                self.removeImageFromSavedList(imgId);
                self.saveImageList();
            });

            $('#maxImgResult').on('focus', function(e) {
                defaultMaxResult = $(this).val();
            });

            $('#maxImgResult').on('change', function(e){
                value = $(this).val();
                if(value == parseInt(value)) {
                    if( value < 1 || value > 100) {
                        $(this).val() < 1 ? $(this).val(1) : $(this).val(100);
                    }
                } else {
                    $(this).val(defaultMaxResult);
                }
            });

            $('[id^=' + id + '_input_]').on('keyup change', function(e){
                if($(this).val()){
                    self.activatePreview(true);
                } else {
                    self.activatePreview( self.checkFormIsFilled());
                }
            });

        }

        self.checkFormIsFilled = function() {
            var filled = false;
            $('[id^=' + id + '_input_]').each(function() {
                if($(this).val()) {
                    filled = true;
                    return false;
                }
            });
            return filled;
        }

        self.activatePreview = function(val){
            if (!val) {
                 $('#'+id+'_previewButton').attr("disabled", "disabled");
            } else {
                 $('#'+id+'_previewButton').removeAttr("disabled");
            }
        }

        self.setSearchMode = function()
        {
            $("#searchMode").val(searchMode);
            if(searchMode == "checkBoxDisable" ){
                $("#maxImgResultDiv").show();
                $("#imgList").hide();
            }else{
                $("#maxImgResultDiv").hide();
                $("#imgList").show();
            }

        }

        self.render = function(targetTag, templateData, html, append) {
            _.templateSettings.variable = "rc";
            var template = _.template( html );
            var rendered = template( templateData )
            if(append){
                $(targetTag).after( rendered );
            }
            else{
                $(targetTag).replaceWith( rendered );
            }
            $( "#sortable" ).sortable({
                    cursor: "move",
                    containment: "parent",
                    update: function( event, ui ) {
                        self.reorderSavedImage();
                    }
            });

            $( "#sortable" ).disableSelection();
        }

        self.sendRequest = function(data) {
            var url = glizyOpt.mediaPicker.replace(/MediaArchive_picker/i, 'EuropeanaShowResults');
            url += '&params=' + encodeURIComponent(data);
            title = "{i18n:Search results}";
            Glizy.closeIFrameDialog(true);
            Glizy.openIFrameDialog( title,
                    url,
                    900,
                    50,
                    50,
                    self.openDialogCallback
                    );
        }

        self.insertSearchParam = function() {
            $(searchFields).each(function (i, searchField) {
                var value = $("#" + id +"_input_" + searchField).val()
                params[searchField] = value || '';
            });

            params['imgCheckBox'] = $( "#searchMode option:selected" ).val();
            params['imgList'] = arrParams['imgList'];
            params['savedImage'] = arrParams['savedImage'];
            params['maxResult'] = $("#maxImgResult").val();
            params['visible'] = self.checkIsResult();
            return JSON.stringify(params);
        }

        self.initImageParams = function() {
            if(arrParams['imgList']){
                checkList = arrParams['imgList'].slice();
            }

            if(arrParams['savedImage']){
                savedImage = arrParams['savedImage'].slice();
            }

        }

        self.checkIsResult = function() {
            if(searchMode == 'checkBoxDisable' && !self.checkFormIsFilled()){
                return false;
            } else if(searchMode == 'checkBoxEnable' && !checkList.length) {
                return false;
            } else if (searchMode == 'checkBoxDisable' && !totalImage ) {
                return false;
            }
            else {
                return true;
            }

        }

        self.removeImageFromSavedList = function(id)
        {
            checkList.splice( $.inArray(id, checkList), 1 );
            savedImage = $.grep(savedImage, function(e) { return e.id!=id });
        }

        self.saveImageList = function() {
             arrParams['imgList'] = checkList.slice();
             arrParams['savedImage'] = savedImage.slice();
             templateData.savedImage = savedImage;
             self.render($("#imgList"), templateData, templateImageList);
        }

        self.reorderSavedImage = function(){
            var tmpSavedImage = [];
            $("#sortable li").each(function(i, el){
                var imgId = $(el).find('i').attr('id');
                var curImage = $.grep(savedImage, function(e) { return e.id==imgId });
                if($.inArray(curImage[0], tmpSavedImage) == -1 && curImage.length > 0){
                    tmpSavedImage.push(curImage[0]);
                }
            });
            savedImage = tmpSavedImage.slice();
            arrParams['savedImage'] = savedImage.slice();
        }

        self.escapeId = function( idToEscape ) {
            return "#" + idToEscape.replace( /(:|\.|\[|\]|\/)/g, "\\$1" );
        }

        self.checkImage = function($frameDocument) {
            $.each(checkList, function (index, id) {
                $(self.escapeId(id), $frameDocument).prop('checked', true);
            });
        }

        self.openDialogCallback = function() {
            var $frame = jQuery(this).children();

            $frame.on("load", function () {
                var $frameDocument = $($frame.contents().get(0));
                totalImage = $("img", $frameDocument).length;
                if( searchMode =='checkBoxEnable')
                {
                    $(".js-controlCheckEnable", $frameDocument).removeClass('hidden');
                    self.checkImage($frameDocument);
                } else{
                    $(".js-controlCheckDisable", $frameDocument).removeClass('hidden');
                }
                $("#imgContainer", $frameDocument).removeClass('hidden');
                $("#loading", $frameDocument).addClass('hidden');

                $frameDocument.on('keyup', function(e) {
                    var charCode = e.charCode || e.keyCode || e.which;
                    if (charCode == 27){
                        checkList = [];
                        Glizy.closeIFrameDialog(true);
                        self.initImageParams();
                    }
                });

                $(".js-glizycms-cancel", $frameDocument).on('click', function(e){
                    checkList = [];
                    Glizy.closeIFrameDialog(true);
                    self.initImageParams();
                });

                  $(".js-glizycms-save", $frameDocument).on('click', function(e){
                    self.saveImageList();
                    Glizy.closeIFrameDialog(true);
                    self.initImageParams();
                });

                $(".js-check", $frameDocument).on('click', function(e){
                    var id = $(this).attr('id');
                    if($(this).attr('checked')){
                        var target = $(this).parent().parent().find('div').find('a');
                        var href = target.attr('href');
                        var src = target.find('img').attr('src');
                        var title =target.attr('title');
                        var image = {   'id' : id,
                                        'title': title,
                                        'href': href,
                                        'src' : src
                                    };
                        savedImage.push(image);
                        checkList.push(id);
                    }else{
                        self.removeImageFromSavedList(id);
                    }

                });

              });
        }

        self.init();

    },

    getValue: function () {
        var self = $(this).data('formEdit');
        var val = self.insertSearchParam();
        $(this).val(val);
        return val;
    },

    setValue: function (value) {
        $(this).val(value);
    },

    destroy: function (field) {
    }

});
