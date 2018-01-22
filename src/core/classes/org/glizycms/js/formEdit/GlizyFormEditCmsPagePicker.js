jQuery.GlizyRegisterType('CmsPagePicker', {

	__construct: function () {
        var el = $(this);
        el.removeAttr('value');

        var controllerName = $(this).data('controllername');
        var filterType = $(this).data('filtertype') || '';
        var protocol = $(this).data('protocol') || '';
        var multiple = $(this).data('multiple');

        
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
                return data.text+' <small>'+data.path+'</small>';
            }
        });
		if (multiple) {
			el.parent().find("ul.select2-choices").sortable({
	            containment: 'parent',
				start: function() { el.select2("onSortStart"); },
			    update: function() { el.select2("onSortEnd"); }
	        });
		}

        this._setValue = function(value) {
            value = value || el.data('origValue');

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
        }

        this._setValue();
	},

	getValue: function () {
        var data = $(this).select2('val');
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