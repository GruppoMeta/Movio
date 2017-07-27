jQuery( (function( $ ){
    var fieldTypes = {};
    var imageAllowExt = 'jpg|jpeg|png|gif|tiff|tif';

    $(document).on('click', 'fieldset.collapsible > legend', function () {
        var $divs = $(this).siblings();
        $divs.toggle();
        $(this).toggleClass('open');
        $(this).parent().toggleClass('open');
    });

    $.GlizyRegisterType = function (type, typeDef) {

        fieldTypes[type] = typeDef;
    };

    $.GlizyRegisterTypeGet = function (type) {
        return fieldTypes[type];
    };

    $.fn.GlizyFormEdit = function(glizyOpt) {

        $.fn.syncRecords = function () {

            return this.each(function () {
                jQuery(this).find('.GFERowContainer').each(function (i) {
                    jQuery(this).toggleClass('GFERowContainerOdd', !!(i % 2))
                        .find('.GFERecordId').text(i +1)
                        .end().find('[name]').each(function () {

                            var name = this.name,
                                newName = name.replace(/\[\d*\]$/, '[' + i + ']');

                            overloadCaller.call(this, 'destroy');
                            this.name = newName;
                            $(this).data('origValue', $(this).data('oldVal'));
                            overloadCaller.call(this, '__construct');
                        });
                });
            });
        };

        $.fn.makeSortable = function () {

            return this.sortable({
                items: '.GFERowContainer',
                handle: '.GFERowHandler',
                start: function (ev) {
                    jQuery(ev.target).find('[name]').each(function () {
                        overloadCaller.call(this, 'destroy');
                    });
                },
                stop: function (ev) {
                    jQuery(ev.target).syncRecords();
                }
            });
        };

        var $form = jQuery(this),

            lang = glizyOpt.lang,

            invalidFields = 0,
            customValidationInvalid = false,
            backupData = '',

            cutText = function (text, len, reverse) {

                if (text && text.length > len) {
                    if (reverse) {
                        text = "... " + text.substr(text.length - len);
                    }
                    else {
                        text = text.substr(0, len) + " ...";
                    }
                }
                return text || '';
            },

            htmlStripTags = function (text) {
                return typeof(text) == "string" ? text.replace(/<[a-z\/][^>]*>/gi, '') : '';
            },

            getOptions = function ($anyDesc) {

                var $fieldSet = $anyDesc.closest('fieldset');

                return {
                    isCollapsable: $fieldSet.attr('data-collapsable') == 'true',
                    minRec: parseInt($fieldSet.attr('data-repeatmin') || 0),
                    maxRec: parseInt($fieldSet.attr('data-repeatmax') || 0),
                    noAddRowButton: $fieldSet.attr('data-noAddRowButton') == 'true'
                };
            },

            overloadCaller = function (method) {

                var $this = jQuery(this),
                    type = $this.attr('data-type'),
                    args;
                if (fieldTypes[type] && fieldTypes[type][method]) {

                    if (arguments.length > 1) args = Array.prototype.slice.call(arguments).slice(1);

                    $this.data({overloadCalled: true, formEdit: fieldTypes[type], glizyOpt: glizyOpt});
                    return fieldTypes[type][method].apply(this, args);
                }
                $this.data('overloadCalled', false);
            },

            addRow = function ($fieldSet, $footer, id, justCreated) {
                var fieldSetId = $fieldSet.attr('id');
                $footer.before($fieldSet.data('rowModel').clone(true)
                    .data('justCreated', justCreated || false)
                    .find('.GFERecordId').text(id +1)
                    .end().find('[name]').attr('name', function (i, name) {
                        $(this).data('originalName', $(this).attr('name'));
                        return fieldSetId+'[' + name + '][' + id + ']'
                    }).end()
                );
            },

            filterEditingFields = function () {

                var i, el;

                if (jQuery('.GFEEditingRow').length) {
                    for (i = 0; el = this[i]; i++) {
                        if (jQuery(el).parents('.GFEEditingRow').length) {
                            return jQuery(el);
                        }
                    }
                    return jQuery();
                }
                else {
                    return jQuery(this).first();
                }
            },

            enableValidation = function () {
                $form.validVal({
                    validate: {
                        fields: {
                            hidden: true,
                            filter: function( fields ) {
                                var newFields = [];
                                $(fields).each(function(index, el){
                                    var $el = $(el);
                                    if ($el.parent().parent().css('display')!=='none') {
                                        if (!$el.data('validValField')) {
                                            $form.trigger( "addField.vv", $el );
                                        }
                                        newFields.push(el);
                                    }
                                });
                                return $(newFields);
                            }
                        }
                    },
                    fields: {
                        onValidate: function ($form, language) {
                            overloadCaller.call(this, 'save');
                        },
                        onInvalid: function( $form, language ) {
                            filterEditingFields.call([$(this)]).parent().parent().addClass('GFEValidationError').next().stop();; //.fadeIn();
                        },
                        onValid: function( $form, language ) {
                            var $el = filterEditingFields.call([$(this)]);
                            if ($el.length) {
                                jQuery($el[0]).parent().parent().removeClass('GFEValidationError');
                                $el.removeClass('GFEValidationError').next().stop(); //.fadeOut();
                            }
                        }
                    },
                    form: {
                        onValidate: function () {

                            var error, fieldVals = {};

                            if (glizyOpt.customValidation && typeof(window[glizyOpt.customValidation]) == 'function') {
                                jQuery(this).find('input:not( [type="button"], [type="submit"], [type="reset"] ), textarea, select').each(function () {
                                    if (this.name) fieldVals[this.name] = jQuery(this).val();
                                });
                                if (error = window[glizyOpt.customValidation](fieldVals)) {
                                    Glizy.events.broadcast("glizy.message.showError", {"title": error, "message": ""});
                                    customValidationInvalid = true;
                                }
                            }
                        },
                        onInvalid: function( field_arr, language ) {

                            var $invalidEl = filterEditingFields.call(field_arr);

                            invalidFields = $invalidEl.length;

                            if ($invalidEl.length) {
                                if ($invalidEl.attr('data-type')) {
                                    overloadCaller.call($invalidEl, 'focus');
                                } else {
                                    $invalidEl.focus();
                                }

                                $invalidEl.parent().parent().addClass('GFEValidationError');
                                Glizy.events.broadcast("glizy.message.showError", {"title": lang.errorValidationMsg, "message": ""});
                            }
                        }
                    }
                });
            },

            getFieldPreview = function (val) {
                var preview = overloadCaller.call(this, 'getPreview', val) || val;
                return preview && preview != ' ' ? '<div class="GFEPreviewItem">' + this.parent().prevAll('label').first().text() + ' : ' + cutText(htmlStripTags(preview), 30) + '</div>' : '';
            },

            getFileExtension = function(filename) {
                var re = /(?:\.([^.]+))?$/;
                return (re.exec(filename)[1]).toLowerCase();
            },

            verifySelectWithTarget = function($container) {
                $container.find('select').each(function () {
                    var target = $(this).data('target');
                    if ( target ) {
                        $(this).change(function(e){
                            var sel = this.selectedIndex,
                                name = this.name;
                                states = $(this).data("val_"+sel);
                            var t = target.split(",");
                            states = states.split(",");

                            $(t).each(function(index, val){
                                var elName = name.match(/(\[([^\d])*\])/) ? name.replace(/(\[([^\d])*\])/, "["+val+"]") : val;
                                $container.find("[name]").each(function(){
                                    var $el = $(this);
                                    if ($el.attr("name")==elName) {
                                        if (states[index]=="1") {
                                            $el.closest("div.control-group").show();
                                        } else {
                                            $el.closest("div.control-group").hide();
                                        }
                                    }
                                });

                                if (states[index]=="1") {
                                    $container.find("#"+elName).show().find("[name]").closest("div.control-group").show();
                                } else {
                                    $container.find("#"+elName).hide().find("[name]").closest("div.control-group").hide();
                                }
                            });

                            var container = $(this).closest('.GFERowContainer');
                            if (!container.find('.GFERowCollapsed').length) {
                                var height = container.height();
                                container.find('.GFERowHandler img').css('height', height - 4);
                            }
                        });
                        $(this).trigger("change");
                    }
                });
            },
            setIndexFields = function() {
                // init the index fields;
                // gli indici possono essere
                // index={tipo} es. index=true, index=int, index=text
                // per oggetti complessi
                // index={campo:tipo,campo:tipo} es. id:int,type:text
                var indexFields = {};
                $("input[data-index], select[data-index]").each(function(index, el){
                    var $el = $(el);
                    var name = $el.attr('name');

                    if (m = name.match(/^([^\[]*)\[([^\]]*)]\[(\d+)\]$/)) {
                        name = m[1]+'.'+m[2];
                    }
                    var indexType = $el.data('index').split(',');
                    if (indexType.length==1 && indexType[0].split(':').length==1) {
                        indexFields[name] = indexType[0];
                    } else {
                        $(indexType).each(function(index, el){
                            var parts = el.split(':');
                            indexFields[name+'@'+parts[0]] = parts[1];
                        })
                    }
                });
                $("#__indexFields").val(JSON.stringify(indexFields));
            };


        $form.find('fieldset[data-type=repeat]').each(function () {

            var $fieldSet = jQuery(this),
                fieldSetId = $fieldSet.attr('id'),
                opt = getOptions($fieldSet),
                $fields = $fieldSet.children(':not(legend)'),
                $rowContainer, $footer, firstField, fieldPrev, i,
                addLabel = $fieldSet.data('add-label') || lang.addRecord;

            $fieldSet.addClass('GFEFieldset');
            if (!opt.noAddRowButton) {
                $fieldSet.append('<div class="GFEFooter"><div class="GFEButtonContainer"><input type="button" value="' + addLabel + '" class="btn GFEAddRow"></div><div class="GFEStatusContainer">' + lang.minRecords + opt.minRec + (opt.maxRec ? (lang.maxRecords + opt.maxRec) : '') + '</div><div class="GFESideClearer"></div></div>');
            }
            else {
                $fieldSet.append('<div class="GFEFooter"></div>');
            }

            $fields.wrapAll('<div class="GFERowContainer" />');

            $rowContainer = $fieldSet.children('.GFERowContainer');
            if (opt.isCollapsable) {
                $fields.wrapAll('<div class="GFERowExpanded" />');

                $rowContainer
                    .append('<div class="GFERowCollapsed"><div class="GFERowHeader"><span class="GFERowHandler"><img width="16" height="38" title="' + lang.drag + '" alt="' + lang.drag + '" src="./application/templates/images/dragHandler.gif"></span><span class="GFERecordTitle">' + lang.record + ' <span class="GFERecordId">1</span></span><span class="GFERowPreview"></span></div><div class="GFERowPanel">'+
                            '<a href="#" class=" GFERowEdit" title="' + lang.edit + '" alt="' + lang.edit + '"><span class="btn-icon icon-pencil"></span></a>'+
                            '<a href="#" class=" GFERowDelete" title="' + lang.remove + '" alt="' + lang.remove + '"><span class="btn-icon icon-trash"></span></a>'+
                            '</div><div class="GFESideClearer"></div></div>')
                .children('.GFERowExpanded')
                    .append('<div class="GFERowButtonContainer"><input type="button" value="' + lang.confirm + '" class="btn btn-primary GFERowDoCollapse  GFERowDoConfirm">&nbsp;<input type="button" value="' + lang.cancel + '" class="btn GFERowDoCollapse"></div>')
                    .hide();
            }
            else {
                $rowContainer
                    .append('<span class="GFERowHandler GFERowHandlerExpanded"><img width="16" style="height:' + ($rowContainer.height() - 4) + 'px" title="' + lang.drag + '" alt="' + lang.drag + '" src="./application/templates/images/dragHandler.gif"></span><a href="#" class=" GFERowDelete GFERightIcon" title="' + lang.remove + '" alt="' + lang.remove + '"><span class="btn-icon icon-trash"></span></a>')

            }
            var $rowContainerCloned = $rowContainer.clone(true).find("[name]").removeAttr('id').end();
            $rowContainerCloned.find("[name] [type^=checkbox]").val('');
            $fieldSet.data('rowModel', $rowContainerCloned);
            $rowContainer.remove();

            if (!opt.noAddRowButton) {
                $fieldSet.prepend('<div class="GFEEmptyMessage">' + (opt.minRec ? lang.noRecordEntered1 + opt.minRec + lang.noRecordEntered2 : lang.clickAddRecordButton.replace('%s', addLabel)) + '</div>');
            }

            var repeaterSetValues = function(repeaterValues) {
                $footer = $fieldSet.children('.GFEFooter');
                if (repeaterValues && ( firstField = repeaterValues[$fields.find('[name]').first().attr('name')])) {
                    for (i = 0; i < firstField.length; i++) {
                        fieldPrev = '';
                        addRow($fieldSet, $footer, i);
                        $footer.prev().find('[name]').each(function () {
                                var $this = jQuery(this),
                                    name = this.name.replace(/^[^\[]*\[([^\]]*)]\[\d+\]$/, '$1'),
                                    val;
                                $this.data('glz-inRepeater', true);
                                if (repeaterValues[name] && (val = repeaterValues[name][i]) !== undefined) {
                                    var simpleSelect = ($this.prop("tagName") == "SELECT") && (!$this.data("type"));
                                    if (typeof(val)!=='string' && !simpleSelect) {
                                        val = JSON.stringify(val);
                                    }
                                    $this.val(val).data('oldVal', val);
                                    $this.val(val).data('origValue', val);
                                    if (val) fieldPrev += getFieldPreview.call($this, val);
                                }
                            })
                            .end().find('.GFERowPreview').html(fieldPrev);
                    }
                    $fieldSet.syncRecords().makeSortable()
                        .children('.GFEEmptyMessage').hide();
                }
            }

            if (glizyOpt.formData) {
                repeaterSetValues(glizyOpt.formData[fieldSetId]);
            }

            Glizy.events.on("glizycms.repeater.setValues", function(e) {
                if ($fieldSet.attr('id')!=e.message.targetId) return;
                $fieldSet.children('.GFERowContainer').remove();
                $fieldSet.syncRecords();
                repeaterSetValues(e.message.data);
            });

            Glizy.events.on("glizycms.fileUpload", function(e) {
                if ($fieldSet.attr('id')!=e.message.targetId) return;
                $footer = $fieldSet.children('.GFEFooter');
                $rows = $fieldSet.children('.GFERowContainer');
                addRow($fieldSet, $footer, $rows.length, true);
                $fieldSet.syncRecords().makeSortable();
                var lastRow = $fieldSet.find('.GFERowContainer:last');
                verifySelectWithTarget($fieldSet);
                enableValidation();
                $uploadFilename = lastRow.find('input[name*=uploadFilename]');
                $originalFileName = lastRow.find('input[name*=originalFileName]');
                var ext = getFileExtension(e.message.originalFilename)
                var media_allowDownload = lastRow.find('input[name*=media_allowDownload]');
                var media_watermark = lastRow.find('input[name*=media_watermark]')
                if(ext == 'tif' || ext =='tiff') {
                    if(media_allowDownload.length) {
                        media_allowDownload.prop('checked', false);
                    }
                    if(media_watermark.length) {
                        media_watermark.prop('checked', true);
                    }
                } else {
                    if(media_allowDownload.length) {
                        media_allowDownload.prop('checked', true);
                    }
                }
                var re = new RegExp("\.(" + imageAllowExt + ")$");
                if (!e.message.originalFilename.match(re)){
                    lastRow.find('input[name*=media_watermark]').prop('disabled', 'disabled');
                    lastRow.find('input[name*=media_zoom]').prop('disabled', 'disabled');
                }
                $title = lastRow.find('input[name*=title]');
                if ($title) {
                    $uploadFilename.val(e.message.uploadFilename);
                    $originalFileName.val(e.message.originalFilename);
                    $title.val(e.message.originalFilename.replace(/\.[^/.]+$/, ""));
                }
                if (e.message.preview) {
                    var preview = jQuery('<div class="dropzone-previews"></div>');
                    preview.append(jQuery('#'+e.message.preview).clone());
                    lastRow.append(preview);
                    lastRow.addClass('GFERowWithPreview');
                }
            });
        });

        $form.find(':input[name]').each(function () {
            var $el = $(this);
            var type = $el.attr('data-type');
            var value = glizyOpt.formData[this.name];
            if (!type && value===null) {
                value = '';
            } else if (!type && typeof(value)==='object') {
                value = JSON.stringify(value);
            }
            if ( value !== undefined ) {
                if ($el.prop('type') == 'select-multiple' && typeof(value)==='string' && value != '') {
                    value = JSON.parse(value);
                }
                $el.val(value);
            } else {
                // non c'è il valore salvato nei dati
                // lo imposta con il valore del campo
                value = $el.attr('type')=='checkbox' ? this.checked : $el.val();
            }
            jQuery(this).data('origValue', value || '');
            if ($(this).data('glz-inRepeater')!==true) {
                overloadCaller.call(this, '__construct');
            }
        });


        $('#mediaFileServer').change(function(e) {
            var ext = getFileExtension($(this).val());
            var re = new RegExp("^(" + imageAllowExt + ")$");
            if(ext === 'tif' || ext === 'tiff'){
                $('[name=media_watermark]').prop('checked', true);
                $('[name=media_allowDownload]').prop('checked', false);
                $('[name=media_watermark]').prop('disabled', false);
                $('[name=media_zoom]').prop('disabled', false);
            } else if(!ext.match(re)) {
                $('[name=media_watermark]').prop('disabled', 'disabled');
                $('[name=media_zoom]').prop('disabled', 'disabled');
                $('[name=media_watermark]').prop('checked', false);
                $('[name=media_allowDownload]').prop('checked', true);
            } else {
                $('[name=media_allowDownload]').prop('checked', true);
                $('[name=media_watermark]').prop('checked', false);
                $('[name=media_watermark]').prop('disabled', false);
                $('[name=media_zoom]').prop('disabled', false);
            }
        });

        verifySelectWithTarget($form);
        // enableValidation();

        jQuery('body').append('<div class="GFETranslucentCover"></div>');

        jQuery(document).on('click', '.GFERowDoCollapse', function () {

            var $button = jQuery(this),
                hasConfirmed = $button.hasClass('GFERowDoConfirm'),
                $rowCont = $button.closest('.GFERowContainer'),
                $inputFields = jQuery('input:not([type=button]), textarea', $rowCont),
                fieldPrev = '';

            if (hasConfirmed && ($form.triggerHandler('submitForm') === false && invalidFields || customValidationInvalid)) {
                customValidationInvalid = false;
                return;
            }
            $rowCont.removeClass('GFEEditingRow').children('.GFERowCollapsed').show()
                .end().children('.GFERowExpanded').hide();

            if (hasConfirmed) {
                $inputFields.each(function () {
                    var $this = jQuery(this),
                        val = overloadCaller.call(this, 'getValue') || $this.val();

                    if (val) {
                        $this.data('oldVal', val);
                        fieldPrev += getFieldPreview.call($this, val);
                    }
                });
                jQuery('.GFERowPreview', $rowCont).html(fieldPrev);
                $rowCont.removeData('justCreated');
            }
            else {
                $inputFields.each(function () {

                    var $this = jQuery(this);

                    overloadCaller.call(this, 'setValue', $this.data('oldVal') || '');

                    if (!$this.data('overloadCalled')) {
                        $this.val($this.data('oldVal') || '');
                    }
                    $this.removeClass('GFEValidationError');
                });
                if ($rowCont.data('justCreated')) {
                    $rowCont.remove();
                }
            }

            jQuery('.GFETranslucentCover').hide();
        });

        jQuery(document).on('click', '.GFERowEdit', function (e) {
            e.preventDefault();
            var $container = jQuery(this).closest('.GFERowContainer'),
                $contBound = $container[0].getBoundingClientRect(),
                $window = jQuery(window),
                wHeight = $window.height();

            $container.addClass('GFEEditingRow')
                .children('.GFERowCollapsed').hide()
                .end().children('.GFERowExpanded').show();

            $window.scrollTop($container.offset().top - Math.max((wHeight - $container.height()) / 2, 0));

            jQuery('.GFETranslucentCover').show();
        });

        jQuery(document).on('click', '.GFERowDelete', function (e) {
            e.preventDefault();

            var $container = jQuery(this).closest('.GFERowContainer'),
                $fieldSet = $container.parent(),
                $rows = $fieldSet.children('.GFERowContainer'),
                opt = getOptions($container);

            var i = $container.index() - 1;
            overloadCaller.call($('#fileuploader'), 'removeFile', i);

            if ($rows.length == opt.minRec) {
                Glizy.events.broadcast("glizy.message.showError", {"title": lang.minRecordMsg + opt.minRec, "message": ""});
                return;
            }
            jQuery('.GFEAddRow').removeClass('GButtonDisabled').removeAttr('disabled');
            $container.remove();

            if (!$fieldSet.children('.GFERowContainer').length) {
                jQuery('.GFEEmptyMessage').show();
            }

            $fieldSet.syncRecords();
        });

        jQuery('.GFEAddRow').click(function () {

            var $button = jQuery(this),
                $fieldSet = $button.closest('fieldset'),
                opt = getOptions($fieldSet),
                $rows = $fieldSet.children('.GFERowContainer');

            if ($button.hasClass('GButtonDisabled')) {
                return;
            }
            if (opt.maxRec && $rows.length == opt.maxRec -1) {
                $button.addClass('GButtonDisabled').attr('disabled', 'disabled').blur();
            }
            jQuery('.GFEEmptyMessage').hide();

            addRow($fieldSet, $button.closest('.GFEFooter'), $rows.length, true);

            $fieldSet.syncRecords().makeSortable();
            $fieldSet.find('.GFERowEdit:last').click();

            verifySelectWithTarget($fieldSet);
            enableValidation();
        });

        var setFormButtonStates = function(state) {
            if (state) {
                jQuery('.js-glizycms-cancel').removeAttr('disabled');
                jQuery('.js-glizycms-save').removeAttr('disabled');
            } else {
                jQuery('.js-glizycms-cancel').attr('disabled', 'disabled');
                jQuery('.js-glizycms-savecomment').attr('disabled', 'disabled');
                jQuery('.js-glizycms-save').attr('disabled', 'disabled');
            }
        }
        var saveOrCancelSuccess = function(data, triggerAction, titleChanged) {
            setFormButtonStates(true);
            if (data.evt) {
                window.parent.Glizy.events.broadcast(data.evt, data.message);
                if (!data.continue) {
                    return;
                }
            }
            if (data.url) {
                if (data.target == 'window') {
                    parent.window.location.href = data.url;
                } else {
                    document.location.href = data.url;
                }
            } else if (data.set) {
                $.each(data.set, function(id, value){
                    $('#'+id).val(value);
                });
                Glizy.events.broadcast("glizy.message.showSuccess", {"title": lang.saveSuccessMsg, "message": ""});
            } else if (data.callback) {
                window[data.callback](data);
            } else if (data.errors) {
                // TODO localizzare
                var errorMsg = '<p>'+GlizyLocale.FormEdit.unableToSave+'</p><ul>';
                $.each(data.errors, function(id, value) {
                    errorMsg += '<li><p class="alert alert-error">'+value+'</p></li>';
                });
                Glizy.events.broadcast("glizy.message.showError", {"title": lang.errorValidationMsg+' '+errorMsg, "message": ""});
            } else {
                if (triggerAction) {
                    $(triggerAction).trigger('click');
                } else {
                    Glizy.events.broadcast("glizy.message.showSuccess", {"title": lang.saveSuccessMsg, "message": ""});
                }
            }

            if (titleChanged===true) {
                Glizy.events.broadcast("glizycms.renameTitle");
            }
        }

        var collectFormData = function() {
            var data = {};
            var titleChanged = false;

            $form.find('fieldset[data-type=repeat]').each(function () {
                var el = jQuery(this);
                data[el.attr('id')] = {};
            });
            $form.find('[name]:not([type=button],[type=submit])').each(function () {
                var el = jQuery(this);
                var val = overloadCaller.call(this, 'getValue'), m;

                if (val === undefined) {
                    val = el.val();
                }
                titleChanged = titleChanged || (el.attr('name') == '__title' && val!=el.data('origValue'));

                if (val !== undefined) {
                    // name = this.name.replace(/[^\[]*\[([^\]]*)]\[\d\]$/, '$1'),
                    // if (m = this.name.match(/^(.*)\[(\d+)\]$/)) {
                    if (m = this.name.match(/^([^\[]*)\[([^\]]*)]\[(\d+)\]$/)) {
                        if (!data[m[1]]) data[m[1]] = {};
                        if (!data[m[1]][m[2]]) data[m[1]][m[2]] = [];
                        data[m[1]][m[2]][parseInt(m[3])] = val;
                    }
                    else {
                        data[this.name] = val;
                    }
                }
            });
            return {data: data, titleChanged: titleChanged}
        }

        jQuery('.js-glizycms-cancel').click(function (e) {
            e.preventDefault();
            window.onbeforeunload = null;
            setFormButtonStates(false);
            jQuery.ajax(glizyOpt.AJAXAction, {
                data: jQuery.param({action: $(e.currentTarget).data("action")}),
                type: "POST",
                success: function(data){
                    saveOrCancelSuccess(data);
                }
            });
        });

        jQuery('.js-glizycms-savecomment').click(function (e){
            enableValidation();
            e.preventDefault();
            var $el = jQuery(this);
            var newId = $el.attr('id')+'_textare';
            var isCancelled = false;

            var pos = Glizy.events.on('glizy.closeDialog', function(e){
                Glizy.events.unbind('glizy.closeDialog', pos);
                if (isCancelled) return;
                $el.val(e.message[newId]);
                jQuery('.js-glizycms-save').trigger('click');
                $el.val(undefined);
            })

            var buttons = {};
            buttons[lang.save] = function() {
                            Glizy.closeDialog();
                        };
            buttons[lang.cancel] = function() {
                            isCancelled = true;
                            Glizy.closeDialog();
                        };
            Glizy.openDialog(
                '<div><textarea id="'+newId+'" class="GFEcomments">'+$el.val()+'</textarea></div>',
                {
                    autoOpen: true,
                    height: 300,
                    width: 650,
                    modal: true,
                    title: lang.insertComment,
                    buttons: buttons
                });
        });

        jQuery('.js-glizycms-save').click(function (e) {
            enableValidation();
            setIndexFields();
            e.preventDefault();

            if ($form.triggerHandler('submitForm') === false && invalidFields || customValidationInvalid) {
                customValidationInvalid = false;
                return;
            }

            var repeaterMinValuesError = null;
            $form.find('fieldset[data-type=repeat]').each(function (i, el) {
                var $fieldSet = $(el);
                var minRecords = $fieldSet.attr('data-repeatmin') || 0;
                $rows = $fieldSet.children('.GFERowContainer')
                if ($rows.length < minRecords) {
                    repeaterMinValuesError = $fieldSet.find('legend').html()+': '+lang.minRecordMsg + minRecords;
                    return false;
                }
            });
            if (repeaterMinValuesError) {
                Glizy.events.broadcast("glizy.message.showError", {"title": repeaterMinValuesError, "message": ""});
                return;
            }

            setFormButtonStates(false);
            var formData = collectFormData();
            var data = formData.data;
            var titleChanged = formData.titleChanged;
            backupData = JSON.stringify(formData.data);

            if (glizyOpt.AJAXAction.indexOf('js:')===0) {
                window[glizyOpt.AJAXAction.substr(3)](data);
            } else {
                var triggerAction = $(e.currentTarget).data("trigger");
                jQuery.ajax(glizyOpt.AJAXAction, {
                    data: jQuery.param({action: $(e.currentTarget).data("action"), data: JSON.stringify(data)}),
                    type: "POST",
                    success: function(data){
                        saveOrCancelSuccess(data, triggerAction, titleChanged);
                    }
                });
            }
        });


        if (jQuery('.js-glizycms-save').length || jQuery('.js-glizycms-savecomment').length) {
            // memorizza i dati iniziali per il confronto del backup
            var formData = collectFormData();
            backupData = formData.data;
            for (var k in backupData) {
                if (typeof(backupData[k])=='string' && !backupData[k]) {
                    backupData[k] = glizyOpt.formData[k] ? glizyOpt.formData[k] : '';
                }
            }
            backupData = JSON.stringify(backupData);
            window.onbeforeunload = function exitWarning(e) {
                var formData = collectFormData();
                if (backupData!=JSON.stringify(formData.data)) {
                    var msg = GlizyLocale.FormEdit.interruptProcess;
                    e = e || window.event;
                    // For IE and Firefox prior to version 4
                    if (e) {
                      e.returnValue = msg;
                    }
                    // For Safari
                    return msg;
                }
            };
        }

        Glizy.events.broadcast("glizycms.formEdit.onReady");
        return this;
    };

    $.fn.scrollToVisible = function () {

        var $window = jQuery(window),
            wHeight = $window.height();

        return this.each(function () {

            var contBound = this.getBoundingClientRect();

            if (wHeight < contBound.bottom || contBound.top < 0) {
                $window.scrollTop(jQuery(this).offset().top -10);
            }
        });
    };

})( jQuery ));
