jQuery.GlizyRegisterType('CmsPagePicker', {

	__construct: function () {
        var self = this;
        var el = $(this);
        el.removeAttr('value');


        var controllerName = $(this).data('controllername');
        var filterType = $(this).data('filtertype') || '';
        var protocol = $(this).data('protocol') || '';
        var multiple = $(this).data('multiple');
        var querystring = $(this).data('querystring');

        if (querystring) {
            el.removeClass('span11').addClass('span9');
        }

        el.select2({
            placeholder: '',
            allowClear: true,
            multiple: multiple,
            minimumInputLength: 3,
            ajax: {
                url: Glizy.ajaxUrl + "&controllerName="+controllerName+"&filterType="+filterType+"&protocol="+protocol,
                dataType: 'json',
                quietMillis: 100,
                data: function(term) {
                    return {
                        term: term
                    };
                },
                results: function(data, page ) {
                    return { results: data }
                }
            },
            formatResult: function(data) {
                return data.text+'<br><small>'+data.path+'</small>';
            },
            formatSelection: function(data) {
               el.data('tempValue', self.collectValue())
               return data.text+' <small>'+data.path+'</small>';
            }
        });

		if (multiple) {
			el.parent().find("ul.select2-choices").sortable({
	            containment: 'parent',
				start: function() { el.select2("onSortStart"); },
			    update: function() { el.select2("onSortEnd"); }
	        });
		} else if (querystring) {
            if (!el.hasClass('js-ready')) {
                el.addClass('js-ready');
                el.data('query-string-el', $('<input type="text" class="span2" placeholder="Query String" style="margin-left: 15px"/>').insertAfter(el));
            }
        }

        this._setValue = function(value) {
            value = value || el.data('origValue') || el.data('tempValue');

            if( Object.prototype.toString.call(value)==='[object Array]') {
                value = JSON.stringify(value);
            }

            $.ajax({
                url: Glizy.ajaxUrl + "&controllerName="+controllerName,
                dataType: 'json',
                data: {id: value},
                success: function(data) {
                    el.select2('data', multiple ? data : data[0]);
                }
            });

            if (querystring && !multiple && value) {
                var queryString = value.split(':');
                if (queryString.length>2) {
                    el.data('query-string-el').val(unescape(queryString[2]));
                }
            }
        };

        this.collectValue = function() {
            var value = el.select2('val');
            var queryString = el.data('query-string-el') ? el.data('query-string-el').val() : '';
            return el.data('multiple') || !el.data('querystring') ? value : value +  (queryString ? ':' + escape(queryString) : '');
        };

        this._setValue();
	},

	getValue: function () {
        var data = this.collectValue();
        $(this).data('origValue', JSON.stringify(data));

        return data;
    },

	setValue: function (value) {
        this._setValue(value);
	},

	destroy: function () {
        $(this).select2('destroy');
	}
});
