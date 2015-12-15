jQuery.GlizyRegisterType('selectfrom', {

    __construct: function () {
        var self = this;
        $(this).removeAttr('value');
        $(this).css('width', '500px');

        var fieldName = $(this).data('field') || $(this).attr('name');
        var multiple = $(this).data('multiple');
        var addNewValues = $(this).data('add_new_values');
        var model = $(this).data('model');
        var query = $(this).data('query');
        var proxy = $(this).data('proxy');
        var proxyParams = $(this).data('proxy_params');
        if (typeof(proxyParams)=='string') {
            proxyParams = proxyParams.replace(/##/g,'"');
        } else {
            proxyParams = JSON.stringify(proxyParams);
        }
        var placeholder = $(this).data('placeholder');
        var originalName = $(this).data('originalName');
        var getId = $(this).data('get_id');
		var minimumInputLength = $(this).data('min_input_length') || 0;
        var formatSelection = $(this).data('format_selection');
        var formatResult = $(this).data('format_result');

        if (originalName !== undefined && $(this).data('override')!==false) {
            fieldName = originalName;
        }

        $(this).select2({
        	width: 'off',
            multiple: multiple,
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
                return formatSelection === undefined ? data.text : window[formatSelection](data);
            }
        }).on('change', function(e){
           self._getValue();
        })

        this._setValue = function(value) {
            var baseValue = value || $(this).data('origValue');
            var multiple = $(this).data('multiple');

            try {
                value = JSON.parse(baseValue);
            } catch (e) {
                value = baseValue;
            }
            if (multiple === undefined || multiple === false) {
                if (value) {
                    if (typeof(value)=="object") {
                        $(this).select2('data', value);
                    } else {
                        $(this).select2('data', {id: value, text: value});
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

               $(this).select2('data', arrayVal);
            }
        }

        this._getValue = function() {
            var data;
            if ($(this).data('return_object')) {
                data = $(this).select2('data');
            } else {
                data = $(this).select2('val');
            }
            $(this).data('origValue', JSON.stringify(data));
            return data;
        }

        this._setValue();
    },

    getValue: function () {
        return this._getValue();
    },

    setValue: function (value) {
        if (value) {
            this._setValue(value);
        }
    },

    destroy: function () {
        $(this).select2("destroy").off("change");
    },

    focus: function () {
        $(this).select2('focus');
    }
});