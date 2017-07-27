Glizy.oop.declare("glizy.FormEdit.repeat", {
    $extends: Glizy.oop.get('glizy.FormEdit.standard'),
    id: null,
    idParent: null,
    formValue:null,
    children: [],
    isCollapsable: null,
    minRec: null,
    maxRec: null,
    noAddRowButton: null,
    noEmptyMessage: null,
    customAddRowLabel: null,
    sortable: null,
    glizyOpt: null,
    form: null,
    readOnly: null,
    childActive: null,

    originalId: function () {
      function s4() {
        return Math.floor((1 + Math.random()) * 0x10000)
          .toString(16)
          .substring(1);
      }
      return s4() + s4() + '-' + s4() + '-' + s4() + '-' +
        s4() + '-' + s4() + s4() + s4();
    },

    initialize: function (element, glizyOpt, form, addBtnId, idParent) {
        this.$super(element);
        this.id = element.attr('id');
        this.idParent = idParent || this.id;
        this.glizyOpt = glizyOpt;
        this.form = form;

        if (addBtnId === undefined) {
            addBtnId = this.id;
        }

        var self = this;

        this.getOptions();
        this.addClass('GFEFieldset');
        if (this.minRec > 0 ) {
            this.addClass('required');
        }
        var $fields = this.$element.children(':not(legend)');
        var $fieldSet = this.$element;

		if(this.noEmptyMessage) {
			$fieldSet.children('legend').before('<div class="border-legend"></div>');
		}

        // TODO: spostare tutta la logica di template in un file appaorte (es handlebars.js)
        if (!this.noAddRowButton && !this.readOnly) {
            var label = this.customAddRowLabel ? this.customAddRowLabel : GlizyLocale.Repeater.addRecord;
            this.$element.append('<div class="GFEFooter"><div class="GFEButtonContainer"><input type="button" id="'+addBtnId+'-addRowBtn" value="' + label + '" class="btn GFEAddRow"></div><div class="GFEStatusContainer">' + GlizyLocale.Repeater.minRecords + this.minRec + (this.maxRec ? (GlizyLocale.Repeater.maxRecords + this.maxRec) : '') + '</div><div class="GFESideClearer"></div></div>');
        } else {
            this.$element.append('<div class="GFEFooter"></div>');
        }

        $fields.wrapAll('<div class="GFERowContainer" />');

        var $rowContainer = $fieldSet.children('.GFERowContainer');

        if(!this.readOnly)
        {
          if (this.isCollapsable) {
              $fields.wrapAll('<div class="GFERowExpanded" />');

              var rowhandler = this.sortable ? '<span class="GFERowHandler" title="' + GlizyLocale.Repeater.drag + '"></span>' : '';

                $rowContainer
                    .append('<div class="GFERowCollapsed"><div class="GFERowHeader">'+rowhandler+'<span class="GFERecordTitle">' + GlizyLocale.Repeater.record + ' <span class="GFERecordId">1</span> <span class="GFERecordLabel"></span></span><span class="GFERowPreview"></span></div><div class="GFERowPanel"><i class="icon icon-pencil GFERowEdit" /> <i class="trashButton fa fa-trash btn-icon icon-trash GFERowDelete" /></div><div class="GFESideClearer"></div></div>')
                .children('.GFERowExpanded')
                    .append('<div class="GFERowButtonContainer"><input type="button" value="' + GlizyLocale.Repeater.confirm + '" class="btn btn-primary GFERowDoCollapse  GFERowDoConfirm">&nbsp;<input type="button" value="' + GlizyLocale.Repeater.cancel + '" class="btn GFERowDoCollapse"></div>')
                    .hide();
          }
          else {
              var rowhandler = this.sortable ? '<span class="GFERowHandler GFERowHandlerExpanded" title="' + GlizyLocale.Repeater.drag + '"><i class="fa fa-ellipsis-v dragAndDrop"></i><i class="fa fa-ellipsis-v dragAndDrop"></i></span>' : '';
              $rowContainer
                  .append(rowhandler + '<div class="trashButtonDiv"><i class="trashButton fa fa-trash btn-icon icon-trash GFERowDelete GFERightIcon" ></i></div>')
          }
        }

        $fieldSet.data('rowModel', $rowContainer.clone(true)
            .find('[name]').val('').removeAttr('id').end()
        );

        $rowContainer.remove();

        if (!this.noAddRowButton && !this.readOnly && !this.noEmptyMessage) {
            $fieldSet.prepend('<div class="GFEEmptyMessage">' + (this.minRec ? GlizyLocale.Repeater.noRecordEntered1 + this.minRec + GlizyLocale.Repeater.noRecordEntered2 : GlizyLocale.Repeater.clickAddRecordButton) + '</div>');
			$fieldSet.prepend('<div class="border-legend"></div>');
        }

        this.makeSortable();

        var invalidFields = 0;
        var customValidationInvalid = false;

        $fieldSet.data('instance', self);

        $(this.$element).off("click").on('click', '.GFERowDoCollapse', function () {
            var $button = $(this),
                hasConfirmed = $button.hasClass('GFERowDoConfirm'),
                $rowCont = $button.closest('.GFERowContainer'),
                $inputFields = $('[name]', $rowCont),
                //$inputFields = $('input:not([type=button]), textarea', $rowCont),
                fieldPrev = '';

            // if (hasConfirmed && (form.triggerHandler('submitForm') === false && invalidFields || customValidationInvalid)) {
            //     customValidationInvalid = false;
            //     return;
            // }
            $rowCont.removeClass('GFEEditingRow').children('.GFERowCollapsed').show()
                .end().children('.GFERowExpanded').hide();

            var child = _.find(self.children,{"originalId":$rowCont.data('originalId')});
            var childIndex = _.findIndex(self.children,{"originalId":$rowCont.data('originalId')});

            $('#GFETranslucentCover_'+$rowCont.data('originalId')).remove();

            // TODO rivedere questa parte, forse conviene usare this.children
            if (hasConfirmed) {
                self.registryOldVal($inputFields);
                $('.GFERowPreview', $rowCont).html(fieldPrev);
                $rowCont.removeData('justCreated');
                var orId = $rowCont.data('originalId');
                if (child && orId && orId.indexOf("new-")!==-1) {
                    orId = orId.replace("new-","");
                    child.originalId = orId;
                    $rowCont.data('originalId',orId);
                }
                try{
                    var msg = {
                        formValue:self.getChildValue(child),
                        pos:childIndex
                    };
                    Glizy.events.broadcast("glizy.formEdit2.repeat.setValue",msg);
                    self.updateEtichetta($fieldSet,$rowCont,msg.formValue);
                }
                catch(err){
                    console.log("Errore nel glizy.formEdit2.repeat.setValue");
                }
            }
            else {
                self.registryOldVal($inputFields);
                $('.GFERowPreview', $rowCont).html(fieldPrev);
                $rowCont.removeData('justCreated');
                var orId = $rowCont.data('originalId');
                if (orId && orId.indexOf("new-")!==-1) {
                    $rowCont.remove();
                }
            }
        });

        $(this.$element).on('click', '.GFERowEdit', function () {
            var $container = $(this).closest('.GFERowContainer'),
                $contBound = $container[0].getBoundingClientRect(),
                $window = $(window),
                wHeight = $window.height();
            self.childActive = $($container).data("originalId");

            $container.addClass('GFEEditingRow')
                .children('.GFERowCollapsed').hide()
                .end().children('.GFERowExpanded').show();

            $window.scrollTop($container.offset().top - Math.max((wHeight - $container.height()) / 2, 0));

            $('body').append('<div class="GFETranslucentCover" id="GFETranslucentCover_'+self.childActive+'"></div>');
            $('#GFETranslucentCover_'+self.childActive).show();
        });

        $('#'+addBtnId+'-addRowBtn').off("click").on('click', function () {
            var $button = $(this),
                $fieldSet = $button.parents('fieldset:first'),
                self = $fieldSet.data('instance'),
                $rows = $fieldSet.children('.GFERowContainer');

            if ($button.hasClass('GButtonDisabled')) {
                return;
            }

            if (self.maxRec && $rows.length == self.maxRec - 1) {
                $button.hide();
            }

            var newRowId;

            // Gestisce il caso di riordino dei GFERowContainer
            // oppure la cancellazione di un GFERowContainer tra più GFERowContainer
            if ($rows.length > 0) {
                // gli id dei GFERowContainer in un array
                var rowsId = $rows.map(function() { return $(this).data('id') }).get();
                newRowId = Math.max.apply(Math, rowsId) + 1;
            } else {
                newRowId = 0;
            }

            self.addRow($fieldSet, $button.closest('.GFEFooter'), newRowId, true, undefined, null, true);
            //self.makeSortable();
        });

        Glizy.events.on("glizycms.fileUpload", function(e) {
            if (self.id != e.message.targetId)  {
                return;
            }
            var $footer = $fieldSet.children('.GFEFooter');
            var $rows = $fieldSet.children('.GFERowContainer');

            self.addRow($fieldSet, $footer, $rows.length, true);

            //$fieldSet.syncRecords().makeSortable();
            $fieldSet.find('.GFERowEdit:last').click();

            //self.verifySelectWithTarget($fieldSet);
            //enableValidation();

            var $title = $fieldSet.find('input[name*=title]:last');

            if ($title) {
                $title.val(e.message.fileName.replace(/\.[^/.]+$/, ""));
            }
        });
    },

    registryOldVal:function(fields){
        fields.each(function () {
            var $this = $(this);
            var obj = $this.data('instance');
            if (obj) {
                var val = obj.getValue();
                if (val) {
                    $this.data('oldVal', val);
                    //fieldPrev += getFieldPreview.call($this, val);
                }
            }
        });
    },

    setOldVal:function(fields){
        fields.each(function () {
            var $this = $(this);
            var obj = $this.data('instance');

            if (obj) {
                var val = obj.setValue($(this).data('oldVal') || '');
            }

            if (!$this.data('overloadCalled')) {
                $this.val($this.data('oldVal') || '');
            }
            $this.removeClass('GFEValidationError');
        });
    },

    getFormData:function($form){
        var unindexed_array = $form.serializeArray();
        var indexed_array = {};

        $.map(unindexed_array, function(n, i){
            indexed_array[n['name']] = n['value'];
        });

        return indexed_array;
    },

    verifySelectWithTarget: function($container) {
        $container.find('select').each(function () {
            var target = $(this).data('target');
            if ( target ) {
                $(this).change(function(e){
                    var sel = this.selectedIndex,
                        name = this.name,
                        states = $(this).data("val_"+sel),
                        stateMap = {};
                    var t = target.split(",");
                    states = states.split(",");

                    $(t).each(function(index, val) {
                        stateMap[val] = states[index];
                    });

                    $container.find("[name]").each(function(){
                        var $el = $(this);
                        var state = stateMap[$el.attr("name")];
                        if (state === '1') {
                            $el.closest("div.control-group").show();
                        } else if (state == '0') {
                            $el.closest("div.control-group").hide();
                        }
                    });
                });
                $(this).trigger("change");
            }
        });
    },

    getOptions: function () {
        this.isCollapsable = this.$element.attr('data-collapsable') == 'true';
        this.minRec = parseInt(this.$element.attr('data-repeatmin') || 0);
        this.maxRec = parseInt(this.$element.attr('data-repeatmax') || 0);
        this.noAddRowButton = this.$element.attr('data-noAddRowButton') == 'true';
        this.sortable = this.$element.attr('data-sortable') == 'true' || this.$element.attr('data-sortable') === undefined;
        this.readOnly = this.$element.attr('data-readOnly') == 'true';
        this.noEmptyMessage = this.$element.attr('data-noEmptyMessage') == 'true';
        this.customAddRowLabel = this.$element.attr('data-customAddRowLabel');
    },

    addDeleteHandler : function(containerId) {
        var self = this;

        $('#'+containerId+' > .trashButtonDiv > .GFERowDelete').on('click', function () {
            var $container = $('#'+containerId),
                $fieldSet = $container.parent(),
                $rows = $fieldSet.children('.GFERowContainer');

            var id = $container.data('originalId');
            var instance = $container.data('instance');
            //overloadCaller.call($('#fileuploader'), 'removeFile', i);

            if ($rows.length == 0) {
                //alert(GlizyLocale.Repeater.minRecordMsg + self.minRec);
                return;
            }

            var childIndex = _.findIndex(self.children,{"originalId":id});

            for (var field in self.children[childIndex]) {
                if(field!=="originalId"){
                    var fieldObj = self.children[childIndex][field];
                    fieldObj.destroy();
                }
            }

            self.children.splice(childIndex, 1);
            
            var $footer = $fieldSet.children('.GFEFooter');

            $footer.find('.GFEAddRow').show();
            $container.remove();

            if (!$fieldSet.children('.GFERowContainer').length) {
                $('.GFEEmptyMessage:first', $fieldSet).show();
            }
        });
    },

    getEtichetta:function(fieldSet,id,formValue){
        var self=this;
        var etichetta;
        try{
            etichetta = id===0 || id ? formValue[id][fieldSet.data("etichette")] : formValue[fieldSet.data("etichette")];
            if(typeof etichetta === "object")
                etichetta=etichetta[0];
            if(fieldSet.data('etichette_schema')){
                var schema = fieldSet.data('etichette_schema');
                for(var i=0; i<schema.length; i++){
                    if(etichetta==schema[i].value)
                        etichetta=schema[i].description;
                }
            }
            if(!etichetta){
                var numInserted = 0;
                etichetta = "Valori inseriti: ";
                var form = id===0 || id ? formValue[id] : formValue;
                _.forEach(form,function(value,key){
                    if(numInserted<2 && value && typeof value ==="string"){
                        etichetta+=value + ", ";
                        numInserted++
                    }
                });
                etichetta=etichetta.substring(0,etichetta.length-2);
            }
        }
        catch(err){
            etichetta = "";
        }
        return etichetta;
    },

    updateEtichetta:function(fieldSet,container,formValue){
        var self=this;
        var etichetta = self.getEtichetta(fieldSet,null,formValue);
        if(etichetta)
            etichetta = "- "+etichetta;
        container.find(".GFERecordLabel").text(etichetta);
    },

    addRow: function (fieldSet, footer, id, justCreated, noVerifySelectWithTarget, value, newRow) {
        if(typeof id === "string")
            id=parseInt(id);
        var idParentPrefix = (this.idParent === null) ? '' : this.idParent+'-';
        var self = this;
        var fieldSetId = fieldSet.attr('id');
        var containerId = idParentPrefix+fieldSetId+id;
        var $container = fieldSet.data('rowModel').clone(true);
        //elimino il numero dal titolo del Record (se si vuole ripristinare modificare con la riga sotto)
        $container.find('.GFERecordId').text("");
        //$container.find('.GFERecordId').text(id + 1);
        var etichetta = self.getEtichetta(fieldSet,id,value);
        if(etichetta)
            etichetta = "- "+etichetta;
        $container.find('.GFERecordLabel').text(etichetta);
        $container.attr('id', containerId);

        footer.before($container);

        $('#'+containerId).data('justCreated', justCreated || false);
        $('#'+containerId).data('id', id);
        var orId = newRow ? "new-" + self.originalId() : self.originalId();
        $('#'+containerId).data('originalId', orId);

        this.addDeleteHandler(containerId);

        $('.GFEEmptyMessage:first', fieldSet).hide();

        this.children.push({"originalId":orId});

        $('#'+containerId+' input[name]:not( [type="button"], [type="submit"], [type="reset"] ), '+
          '#'+containerId+' select[name], '+
          '#'+containerId+' textarea[name], '+
          '#'+containerId+' fieldset[data-type]').each(function () {
            var element = $(this);

            var parents = element.parents('[data-type]');

            // se l'elemento è contenuto immediatamente nel repeater
            if (parents[0] == self.$element[0]) {
                var addBtnId = containerId+'-'+$(this).attr('id');
                self.createChild(id, element, addBtnId, containerId);
            }
        });

        if (noVerifySelectWithTarget === undefined) {
            this.verifySelectWithTarget($container);
        }

        return $container;
    },

    createChild: function(rowId, element, addBtnId, containerId) {
        var type = element.data('type') || 'standard';
        var obj = Glizy.oop.create("glizy.FormEdit."+type, element, this.glizyOpt, this.$form, addBtnId, containerId);
        var name = obj.getName();
        var child = this.children.length-1;
        this.children[child][name] = obj;
    },

    makeSortable: function () {
        var from;
        var self = this;
        return this.$element.sortable({
            items: '.GFERowContainer',
            handle: '.GFERowHandler',
            start: function (ev, ui) {
                var isTinymce = $(ui.item[0]).find("textarea[data-type='tinymce']");
                if(isTinymce.length){
                    _.forEach(isTinymce,function(value){
                        tinyMCE.execCommand('mceRemoveControl', false, value.id);
                    })
                }
                from = ui.item.index()-2;
            },
            stop: function (ev, ui) {
                var to = ui.item.index()-2;

                // sposta un elemento nell'array da from a to
                function arraymove(arr, from, to) {
                    var element = arr[from];
                    arr.splice(from, 1);
                    arr.splice(to, 0, element);
                }

                if (from !== to) {
                    arraymove(self.children, from-1, to-1);
                }
                var isTinymce = $(ui.item[0]).find("textarea[data-type='tinymce']");
                if(isTinymce.length){
                    _.forEach(isTinymce,function(value){
                        tinyMCE.execCommand('mceAddControl', false, value.id);
                    })
                }
            }
        });
    },

    getChildValue: function (child) {
        var row = child;
        var obj = {};
        for (var field in row) {
            if(field!=="originalId"){
                var fieldObj = row[field];
                if (!fieldObj.isDisabled()) {
                    var val = fieldObj.getValue();
                    obj[field] = val;
                }
            }
        }

        return obj;
    },

    getValue: function () {
        var data = [];
        for (var i in this.children) {
            var row = this.children[i];
            var obj = {};
            for (var field in row) {
                if(field!=="originalId"){
                    var fieldObj = row[field];

                    if (!fieldObj.isDisabled()) {
                        var val = fieldObj.getValue();
                        obj[field] = val;
                    }
                }
            }
            data.push(obj);
        }

        return data;
    },

    setValue: function (value) {
        value = this.convertFromOldFormat(value);

        if (value && value.length > 0) {
            var $fieldSet = this.$element;
            var $footer = $fieldSet.children('.GFEFooter');

            for (var i in value) {
                var $container = this.addRow($fieldSet, $footer, i, true, true, value);
                var row = value[i];
                for (var field in row) {
                    if(field!=="originalId"){
                        var v = row[field];
                        var obj = this.children[i][field];

                        if (obj) {
                            obj.setValue(v);
                        }
                    }
                }
                var $inputFields = $('[name]', $container);
                this.verifySelectWithTarget($container);

                if (this.maxRec && i >= this.maxRec - 1) {
                    $footer.find('.GFEAddRow').hide();
                }
            }
        }
    },

    getName: function () {
        return this.id;
    },

    focus: function() {
        $('html, body').animate({ scrollTop: this.$element.offset().top - this.$element.prop('scrollHeight')}, 'slow');
    },

    isValid: function() {
        if (this.minRec == 0 || this.children.length >= this.minRec) {
            var isValid = true;
            for (var i in this.children) {
                var row = this.children[i];
                for (var field in row) {
                    if(field!=="originalId"){
                        var fieldObj = row[field];

                        if (!fieldObj.isValid()) {
                            fieldObj.addClass('GFEValidationError');
                            fieldObj.getElement().parents('.GFERowContainer').addClass('GFEValidationError');
                            isValid = false;
                        } else {
                            fieldObj.removeClass('GFEValidationError');
                            if(isValid)
                            {
                              fieldObj.getElement().parents('.GFERowContainer').removeClass('GFEValidationError');
                            }
                        }
                    }
                }
            }
            return isValid;
        } else {
            return false;
        }
    },

    /**
     * Check anche convert the values if are stored with old format
     * @param  object|array value
     * @return array
     */
    convertFromOldFormat: function(value) {
        return value;
        if (value) {
            var keys = Object.keys(value),
                canConvert = true,
                numItems;

            keys.forEach(function (item) {
                if (Object.prototype.toString.call(value[item]) === '[object Array]' ) {
                    if (!numItems) {
                        numItems = value[item].length;
                    }
                    canConvert = canConvert && numItems==value[item].length;
                }
            });

            if (canConvert) {
                var newValue = [];
                for (var i=0; i<numItems; i++) {
                    var tempItem = {};
                    keys.forEach(function (item) {
                        tempItem[item] = value[item][i];
                    });
                    newValue.push(tempItem);
                }

                value = newValue;
            }
        }
        return value;
    }
});
