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
    addBtnId: null,

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
        this.addBtnId = addBtnId === undefined ? this.id : addBtnId;
        if (this.glizyOpt.readOnly) {
            this.readOnly = true;
        }

        this.getOptions();
        this.templateDefine();
        this.initializeView();
        this.makeSortable();
        this.initializeEvents();
    },

    initializeView: function() {
        this.addClass('GFEFieldset');
        if (this.minRec > 0 ) {
            this.addClass('required');
        }
        var $fields = this.$element.children(':not(legend)');
        var $fieldSet = this.$element;

    	$fieldSet.children('legend').before(Glizy.template.render('glizycms.FormEditRerpeater.noEmptyMessage', {message: this.noEmptyMessage}));
        this.$element.append(Glizy.template.render('glizycms.FormEditRerpeater.footer', {
                    canAdd: !this.noAddRowButton && !this.readOnly,
                    addBtnId: this.addBtnId,
                    label: this.customAddRowLabel ? this.customAddRowLabel : GlizyLocale.Repeater.addRecord,
                    minRecords: GlizyLocale.Repeater.minRecords + this.minRec + (this.maxRec ? (GlizyLocale.Repeater.maxRecords + this.maxRec) : '')
                }));
        $fields.wrapAll(Glizy.template.render('glizycms.FormEditRerpeater.wrapFields'));
        var $rowContainer = $fieldSet.children('.GFERowContainer');

        if(!this.readOnly) {
            var templateData = {
                sortable: this.sortable,
                labelDrag: GlizyLocale.Repeater.drag,
                labelRecord: GlizyLocale.Repeater.record,
                labelConfirm: GlizyLocale.Repeater.confirm,
                labelCancel: GlizyLocale.Repeater.cancel,
            };
            if (this.isCollapsable) {
                $fields.wrapAll(Glizy.template.render('glizycms.FormEditRerpeater.wrapCollapsable'));
                $rowContainer.append(Glizy.template.render('glizycms.FormEditRerpeater.rowCollapsable', templateData))
                    .children('.GFERowExpanded')
                    .append(Glizy.template.render('glizycms.FormEditRerpeater.rowCollapsableButtons', templateData))
                    .hide();
          } else {
            $rowContainer.append(Glizy.template.render('glizycms.FormEditRerpeater.row', templateData))
          }
        }

        $fieldSet.data('rowModel', $rowContainer.clone(true)
            .find('[name]').val('').removeAttr('id').end()
        );

        $rowContainer.remove();
        if (!this.noAddRowButton && !this.readOnly && !this.noEmptyMessage) {
            $fieldSet.prepend(Glizy.template.render('glizycms.FormEditRerpeater.addMessage', {label: this.minRec ? GlizyLocale.Repeater.noRecordEntered1 + this.minRec + GlizyLocale.Repeater.noRecordEntered2 : GlizyLocale.Repeater.clickAddRecordButton}))
        }
    },

    initializeEvents: function() {
        $(this.$element).off("click")
            .on('click', '.GFERowDoCollapse', Glizy.responder(this, this.handleDoCollapse))
            .on('click', '.GFERowEdit', Glizy.responder(this, this.handleEdit))
            .on('click', '.GFERowDelete', Glizy.responder(this, this.handleDelete))
            .on('click', '.GFERowShow', Glizy.responder(this, this.handleShow))
            .on('click', '.GFERowHide', Glizy.responder(this, this.handleHide));

        $('#'+this.addBtnId+'-addRowBtn').off("click").on('click', Glizy.responder(this, this.handleAddRecord));
        Glizy.events.on("glizycms.fileUpload", Glizy.responder(this, this.handleFileUpload));
    },

    handleDoCollapse: function(e) {
        var $button = $(e.currentTarget),
            hasConfirmed = $button.hasClass('GFERowDoConfirm'),
            $rowCont = $button.closest('.GFERowContainer'),
            $inputFields = $('[name]', $rowCont),
            fieldPrev = '',
            child = _.find(this.children, {"originalId": $rowCont.data('originalId')}),
            childIndex = _.findIndex(this.children, {"originalId":$rowCont.data('originalId')});

        if (hasConfirmed && !this.isValidRow(childIndex)) {
            return;
        }

        $rowCont.removeClass('GFEEditingRow')
                .children('.GFERowCollapsed').show()
                .end().children('.GFERowExpanded').hide();
        $('#GFETranslucentCover_'+$rowCont.data('originalId')).remove();
        $('.GFERowPreview', $rowCont).html(fieldPrev);

        $rowCont.removeData('justCreated');
        var orId = $rowCont.data('originalId');

        if (!hasConfirmed) {
            this.setOldVal($inputFields);
            this.verifySelectWithTarget($rowCont);
            if (orId && orId.indexOf("new-")!==-1) {
                $rowCont.remove();
            }
            return;
        }

        this.registryOldVal($inputFields);
        if (child && orId && orId.indexOf("new-")!==-1) {
            orId = orId.replace("new-","");
            child.originalId = orId;
            $rowCont.data('originalId',orId);
        }

        try {
            var msg = {
                formValue:this.getChildValue(child),
                pos:childIndex
            };
            Glizy.events.broadcast("glizy.formEdit2.repeat.setValue",msg);
            this.updateEtichetta(this.$element, $rowCont, msg.formValue, null);
            this.updateVisibility($rowCont, msg.formValue);
        }
        catch(err){
            console.log("Errore nel glizy.formEdit2.repeat.setValue");
        }
    },

    handleEdit: function(e) {
        var $container = $(e.currentTarget).closest('.GFERowContainer'),
            $inputFields = $('[name]', $container),
            $contBound = $container[0].getBoundingClientRect(),
            $window = $(window),
            wHeight = $window.height(),
            coverId = 'GFETranslucentCover_'+$($container).data("originalId");

        $container.addClass('GFEEditingRow')
            .children('.GFERowCollapsed').hide()
            .end().children('.GFERowExpanded').show();

        this.registryOldVal($inputFields);
        $window.scrollTop($container.offset().top - Math.max((wHeight - $container.height()) / 2, 0));

        $('body').append(Glizy.template.render('glizycms.FormEditRerpeater.overlay', {id: coverId}));
        $('#'+coverId).show();
    },

    handleDelete: function(e) {
        var $container = $(e.currentTarget).closest('.GFERowContainer'),
            $fieldSet = $container.parent(),
            $rows = $fieldSet.children('.GFERowContainer'),
            id = $container.data('originalId');

        if ($rows.length == 0) {
            return;
        }

        var childIndex = _.findIndex(this.children, {"originalId": id});

        for (var field in this.children[childIndex]) {
            if(field!=="originalId"){
                var fieldObj = this.children[childIndex][field];
                fieldObj.destroy();
            }
        }

        this.children.splice(childIndex, 1);
        var $footer = $fieldSet.children('.GFEFooter');
        $footer.find('.GFEAddRow').show();
        $container.remove();

        if (!$fieldSet.children('.GFERowContainer').length) {
            $('.GFEEmptyMessage:first', $fieldSet).show();
        }
    },

    handleAddRecord: function(e) {
        var $button = $(e.currentTarget),
            $fieldSet = $button.parents('fieldset:first'),
            $rows = $fieldSet.children('.GFERowContainer');

        if ($button.hasClass('GButtonDisabled')) {
            return;
        }

        if (this.maxRec && $rows.length == this.maxRec - 1) {
            $button.hide();
        }

        var newRowId = 0;
        // Gestisce il caso di riordino dei GFERowContainer
        // oppure la cancellazione di un GFERowContainer tra più GFERowContainer
        if ($rows.length > 0) {
            // gli id dei GFERowContainer in un array
            var rowsId = $rows.map(function() { return $(this).data('id') }).get();
            newRowId = Math.max.apply(Math, rowsId) + 1;
        }

        var newRowContainer = this.addRow($fieldSet, $button.closest('.GFEFooter'), newRowId, true, undefined, null, true);
        newRowContainer.find('.GFERowEdit').trigger('click');
    },

    handleFileUpload: function(e) {
        if (this.id != e.message.targetId)  {
            return;
        }
        var $footer = this.$element.children('.GFEFooter');
        var $rows = this.$element.children('.GFERowContainer');

        var newRowContainer = this.addRow(this.$element, $footer, $rows.length, true);
        newRowContainer.find('.GFERowEdit').trigger('click');

        var $title = this.$element.find('input[name*=title]:last');
        if ($title) {
            $title.val(e.message.fileName.replace(/\.[^/.]+$/, ""));
        }
    },

    handleShow: function(e) {
        this.changeRowVisibility($(e.currentTarget).closest('.GFERowContainer'), true);
    },

    handleHide: function(e) {
        this.changeRowVisibility($(e.currentTarget).closest('.GFERowContainer'), false);
    },

    registryOldVal:function(fields){
        fields.each(function () {
            var $this = $(this);
            var obj = $this.data('instance');
            if (obj) {
                var val = obj.getValue();
                if (val) {
                    $this.data('oldVal', val);
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
                        stateMap[val] = states[index]==="1";

                        if (stateMap[val]) {
                            $container.find("#"+val).show().find("[name]").data('skip-validation', false).closest("div.form-group,div.control-group").show();
                        } else {
                            $container.find("#"+val).hide().find("[name]").data('skip-validation', true).closest("div.form-group,div.control-group").hide();
                        }
                    });

                    $container.find("[name]").each(function(){
                        var $el = $(this);
                        var state = stateMap[$el.attr("name")];
                        if (state===true) {
                            $el.data('skip-validation', false).closest("div.form-group,div.control-group").show();
                        } else if (state===false) {
                            $el.data('skip-validation', true).closest("div.form-group,div.control-group").hide();
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
                etichetta = "";
                var form = id===0 || id ? formValue[id] : formValue;
                _.forEach(form,function(value,key){
                    if(numInserted<2 && value && typeof value ==="string"){
                        etichetta+= (value.length > 30 ?  value.substring(0, 30)+'...' : value) + ", ";
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

    updateEtichetta:function(fieldSet, container, formValue, rowCount){
        var etichetta = this.getEtichetta(fieldSet, rowCount, formValue);
        if(etichetta) etichetta = "- "+etichetta;
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
        $container.find('.GFERecordId').text(id + 1);
        $container.attr('id', containerId);
        this.updateEtichetta(fieldSet, $container, value, id);
        this.updateVisibility($container, value ? value[id] : {});

        footer.before($container);

        $('#'+containerId).data('justCreated', justCreated || false);
        $('#'+containerId).data('id', id);
        var orId = newRow ? "new-" + self.originalId() : self.originalId();
        $('#'+containerId).data('originalId', orId);

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
                var id = $(this).attr('id') ? $(this).attr('id') : $(this).attr('name');
                var addBtnId = containerId+'-'+id;
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
        var obj = Glizy.oop.create("glizy.FormEdit."+type, element, this.glizyOpt, this.form, addBtnId, containerId);
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
        if (this.children.length > 0) {
            this.children = [];
            this.$element.find('.GFERowDelete').trigger('click');
        }

        value = this.convertFromOldFormat(value);

        if (value && value.length > 0) {
            var $fieldSet = this.$element;
            var $footer = $fieldSet.children('.GFEFooter');

            for (var i in value) {
                var row = value[i];
                if (_.isEmpty(row)) {
                    continue;
                }
                var $container = this.addRow($fieldSet, $footer, i, true, true, value);
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
                isValid = this.isValidRow(i, isValid);
            }
            return isValid;
        } else {
            return false;
        }
    },

    isValidRow: function(i, isValid) {
        isValid = isValid || true;
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

        return isValid;
    },

    updateVisibility: function(container, formValue) {
        var hideState = false,
            showState = false,
            hideElement = container.find('.GFERowHide'),
            showElement = container.find('.GFERowShow');

        if(formValue.hasOwnProperty('isVisible')){
            hideState = formValue.isVisible===true;
            showState = formValue.isVisible!==true;
        }

        hideElement.css('display', hideState ? 'inline-block' : 'none');
        showElement.css('display', showState ? 'inline-block' : 'none');
    },

    changeRowVisibility: function(container, state) {
        var child = _.find(this.children, {"originalId": container.data('originalId')}),
            childIndex = _.findIndex(this.children, {"originalId": container.data('originalId')});

        for (var field in child) {
            if(field!=="isVisible") continue;
            var fieldObj = child[field];
            fieldObj.setValue(state);
            break;
        }
        this.updateVisibility(container, this.getChildValue(child));
    },

    /**
     * Check anche convert the values if are stored with old format
     * @param  object|array value
     * @return array
     */
    convertFromOldFormat: function(value) {
        if (value && typeof(value)==='object' && !Array.isArray(value)) {
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
    },


    templateDefine: function() {
        Glizy.template.define('glizycms.FormEditRerpeater.noEmptyMessage',
            '<% if (message) { %>'+
            '<div class="border-legend"></div>'+
            '<% } %>'
        );
        Glizy.template.define('glizycms.FormEditRerpeater.wrapFields', '<div class="GFERowContainer"></div>');
        Glizy.template.define('glizycms.FormEditRerpeater.wrapCollapsable','<div class="GFERowExpanded"></div>');
        Glizy.template.define('glizycms.FormEditRerpeater.footer',
            '<div class="GFEFooter">'+
                '<% if (canAdd) { %>'+
                '<div class="GFEButtonContainer">'+
                    '<input type="button" id="<%= addBtnId %>-addRowBtn" value="<%= label %>" class="btn GFEAddRow">'+
                '</div>'+
                '<div class="GFEStatusContainer"><%= minRecords %></div>'+
                '<div class="GFESideClearer"></div>'+
                '<% } %>'+
            '</div>'
        );
        Glizy.template.define('glizycms.FormEditRerpeater.rowCollapsable',
            '<div class="GFERowCollapsed">'+
                '<div class="GFERowHeader">'+
                    '<% if (sortable) { %>'+
                        '<span class="GFERowHandler GFERowHandlerExpanded" title="<%= labelDrag %>"></span>'+
                    '<% } %>'+
                    '<span class="GFERecordTitle" style="margin-left: 20px"><%= labelRecord %> <span class="GFERecordId"></span> <span class="GFERecordLabel"></span></span><span class="GFERowPreview"></span>'+
                '</div>'+
                '<div class="GFERowPanel"><i class="fa fa-eye icon-eye-open btn-icon GFERowHide" /> <i class="icon-eye-close btn-icon GFERowShow" /> <i class="fa fa-pendin btn-icon icon-pencil GFERowEdit" /> <i class="trashButton fa fa-trash btn-icon icon-trash GFERowDelete" /></div>'+
                '<div class="GFESideClearer"></div>'+
            '</div>'
        );
        Glizy.template.define('glizycms.FormEditRerpeater.rowCollapsableButtons',
            '<div class="GFERowButtonContainer">'+
                '<input type="button" value="<%= labelConfirm %>" class="btn btn-primary GFERowDoCollapse  GFERowDoConfirm">&nbsp;<input type="button" value="<%= labelCancel %>" class="btn GFERowDoCollapse">'+
            '</div>'
        );
        Glizy.template.define('glizycms.FormEditRerpeater.row',
            '<% if (sortable) { %>'+
                '<span class="GFERowHandler GFERowHandlerExpanded" title="<%= labelDrag %>"><i class="fa fa-ellipsis-v dragAndDrop"></i><i class="fa fa-ellipsis-v dragAndDrop"></i></span>'+
            '<% } %>'+
            '<div class="trashButtonDiv">'+
                '<i class="trashButton fa fa-trash btn-icon icon-trash GFERowDelete GFERightIcon" ></i>'+
            '</div>'
        );
        Glizy.template.define('glizycms.FormEditRerpeater.addMessage',
            '<div class="border-legend"></div>'+
            '<div class="GFEEmptyMessage"><%= label %></div>'
        );
        Glizy.template.define('glizycms.FormEditRerpeater.overlay',
            '<div class="GFETranslucentCover" id="<%= id %>"></div>'
        );
    }
});
