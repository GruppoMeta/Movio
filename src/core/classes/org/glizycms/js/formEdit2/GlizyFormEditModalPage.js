Glizy.oop.declare("glizy.FormEdit.modalPage", {
    $extends: Glizy.oop.get('glizy.FormEdit.standard'),
    pageId: null,
    modalDivId: null,
    modalIFrameId: null,

    initialize: function (element) {
        element.data('instance', this);
        this.$element = element;
        this.pageId = element.data('pageid');
        var controller = element.data('controller');
        var model = element.data('model');

        this.modalDivId = 'modalDiv-'+element.attr('id');
        this.modalIFrameId = 'modalIFrame-'+element.attr('id');

        var that = this;

        element.select2({
            placeholder: '',
            allowClear: true,
            ajax: {
                url: Glizy.ajaxUrl + '&controllerName='+controller,
                dataType: 'json',
                quietMillis: 250,
                data: function(term, page) {
                    return {
                        fieldName: 'fieldName',
                        model: model,
                        term: term
                    };
                },
                results: function(data, page ) {
                    data.result.push({id:'__addNewEntry', 'text':'» ' + GlizyLocale.FormEdit.addRecord});
                    return { results: data.result }
                }
            },
            formatSelection: function(data) {
                if (data.id == '__addNewEntry') {
                    that.openModal();
                } else {
                    if (data.values === undefined) {
                        $.ajax({
                            url: Glizy.ajaxUrl + '&controllerName='+controller,
                            dataType: "json",
                            data: {
                                id: data.id,
                                model: model
                            }
                        }).done(function(data) {
                            if(data.result[0])
                            {
                              that.populateData(data.result[0].values);
                            }
                        });
                    } else {
                        that.populateData(data.values);
                    }
                    return data.text;
                }
            }
        })
        .on("change", function(e) {
            if (e.val == '') {
                that.clearData();
            }
        });

        Glizy.events.on('glizy.FormEdit.modalPage.message', this.receiveMessage);
    },

    populateData: function(values) {
        // TODO: slegare il componente dal repeater
        var $container = this.$element.closest('.GFERowContainer');

        for (var field in values) {
            var $el = $container.find('input[name='+field+'],textarea[name='+field+']');
            if ($el) {
                var obj = $el.data('instance');

                if (obj) {
                    obj.setValue(values[field]);
                }
            }
        }
    },

    clearData: function() {
        // TODO: slegare il componente dal repeater
        var $container = this.$element.closest('.GFERowContainer');
        $container.find('input[disabled=disabled]').val('');
    },

    receiveMessage: function (event)
    {
        Glizy.closeIFrameDialog(true);

        var $element = $('.__selectedModalPage');

        var msg = event.message;

        if (msg.type == 'save') {
            $element.select2('data', {id: msg.id, text: msg.text, values: msg.values});
        }

        $element.removeClass('__selectedModalPage');
    },

    openDialogCallback: function() {
    },

    openModal: function() {
        var w = Math.min( $( window ).width() - 50, 900 );

        this.$element.addClass('__selectedModalPage');

        Glizy.openIFrameDialog(
            '',
			'index.php?pageId='+this.pageId,
			w,
			50,
			50,
			this.openDialogCallback
        );

        var self = this;

        $("#modalDiv").on('dialogclose', function(event){
            var data = self.$element.select2('data');
            if (data.id === '__addNewEntry') {
                self.$element.select2('data', null);
            }
        });
	},

    getValue: function () {
        var data = this.$element.select2('data');

        if (data) {
            return {id: data.id, text: data.text};
        } else {
            return null;
        }
    },

    setValue: function (value) {
        if (value) {
            this.$element.select2('data', value);
        }
    },

    getName: function () {
        return this.$element.attr('name');
    },

    focus: function () {
        this.$element.select2('focus');
    },

    destroy: function() {
    },

    isDisabled: function() {
        return this.$element.attr('disabled') == 'disabled';
    },

    addClass: function(className) {
        this.$element.addClass(className);
    },

    removeClass: function(className) {
        this.$element.removeClass(className);
    }
});
