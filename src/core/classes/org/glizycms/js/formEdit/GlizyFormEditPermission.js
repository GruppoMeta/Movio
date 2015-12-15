jQuery.GlizyRegisterType('permission', {

	__construct: function () {
        $(this).removeAttr('value');
        $(this).css('width', '500px');
        
        var value = jQuery(this).data('origValue');
    
        if (value !== undefined && value.length > 0) {
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
	},

	getValue: function () {
        return $(this).select2('val');
	},

	setValue: function (value) {
        if (value[0].id) {
            $(this).select2('data', value);
        }
	},

	destroy: function () {
	}
});