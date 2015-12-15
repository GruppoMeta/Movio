Glizy.oop.declare("glizy.FormEdit.selectfrom", {
    $extends: Glizy.oop.get('glizy.FormEdit.standard'),
    multiple: null,
    
    initialize: function (element) {
        element.data('instance', this);
        this.$element = element;
        
        element.removeAttr('value');
        element.css('width', '500px');

        var fieldName = element.data('field') || element.attr('name');
        this.multiple = element.data('multiple');
        var addNewValues = element.data('add_new_values');
        var model = element.data('model');
        var query = element.data('query');
        var proxy = element.data('proxy');
        var proxyParams = element.data('proxy_params');
        if (proxyParams) {
            proxyParams = proxyParams.replace(/##/g,'"');
        }
        var placeholder = element.data('placeholder');
        var originalName = element.data('originalName');
        var getId = element.data('get_id');
        var selectedCallback = element.data('selected_callback');
    	var minimumInputLength = $(this).data('min_input_length') || 0;
    	var formatSelection = $(this).data('format_selection');
        var formatResult = $(this).data('format_result');

        if (originalName !== undefined && element.data('override')!==false) {
            fieldName = originalName;
        }

        element.select2({
        	width: 'off',
            multiple: this.multiple,
            minimumInputLength: minimumInputLength,
            placeholder: placeholder === undefined ? '' : placeholder,
            allowClear: true,
            ajax: {
                url: Glizy.ajaxUrl + "&controllerName=org.glizycms.contents.controllers.autocomplete.ajax.FindTerm",
                dataType: 'json',
                quietMillis: 250,
                data: function(term, page) {
                    return {
                        fieldName: fieldName,
                        model: model,
                        query: query,
                        term: term,
                        proxy: proxy,
                        proxyParams: proxyParams,
                        getId: getId
                    };
                },
                results: function(data, page ) {
                    return { results: data.result }
                }
            },
            createSearchChoice: function(term, data) {
                if (!addNewValues) {
                    return false;
                }

                if ($(data).filter(function() {
                    return this.text.localeCompare(term)===0;
                }).length===0) {
                    return {id:term, text:term};
                }
            },
            formatResult: function(data) {
                return formatResult === undefined ? data.text : window[formatResult](data);
            },
            formatSelection: function(data) {
                if (selectedCallback) {
                    var term = data.text;
                    
                    $.ajax({
                        url: Glizy.ajaxUrl+"&controllerName="+selectedCallback,
                        data: {
                            fieldName: fieldName,
                            model: model,
                            query: query,
                            term: term,
                            proxy: proxy,
                            proxyParams: proxyParams,
                            getId: getId
                        },
                        type: "POST"
                    });
                }
    
                return formatSelection === undefined ? data.text : window[formatSelection](data);
            }
        });
    },
    
    getValue: function () {
        if (this.$element.data('return_object')) {
            return this.$element.select2('data');
        } else {
            return this.$element.select2('val');
        }
    },
    
    setValue: function (value) {
        if (!this.multiple) {
            if (value) {
                if (typeof(value)=="object") {
                    this.$element.select2('data', value);
                } else {
                    this.$element.select2('data', {id: value, text: value});
                }
            }
        }
        else if (value !== undefined && value.length > 0) {
            var arrayVal = []

            $.each(value, function(index, v) {
                if (typeof(v)=="object") {
                    arrayVal.push(v);   
                }
                else {
                    arrayVal.push({id: v, text: v});
                }
            });

           this.$element.select2('data', arrayVal);
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