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

        var value = el.data('origValue');
        if (value) {
            $.ajax({
                url: Glizy.ajaxUrl + "&controllerName="+controllerName,
                dataType: 'json',
                data: {id: value},
                success: function(data) {
                    el.select2('data', multiple ? data : data[0]);
                }
            });
        }
	},

	getValue: function () {
        return $(this).select2('val');
	},

	setValue: function (value) {
        if (value && value.length && value[0].id) {
            $(this).select2('data', value);
        }
	},

	destroy: function () {
        $(this).data('origValue', $(this).val());
        $(this).select2('destroy');
	}
});