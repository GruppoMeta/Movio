if (jQuery.GlizyRegisterType) {
    jQuery.GlizyRegisterType('entityselect', {

		__construct: function () {
            $(this).removeAttr('value');
            
            $(this).css('width', '500px');
           
            var entityTypeId = $(this).data('entity_type_id');
            var cardinality = $(this).data('cardinality');

            $(this).select2({
                multiple: cardinality == 1, // relazione uno a molti
                placeholder: '',
                allowClear: true,
                ajax: {
                    url: Glizy.ajaxUrl + "&controllerName=movio.modules.ontologybuilder_controllers.entityFormEdit.ajax.FindEntities",
                    dataType: 'json',
                    quietMillis: 100,
                    data: function(term, page) {
                        return {
                            entityTypeId: entityTypeId,
                            term: term
                        };
                    },
                    results: function(data, page ) {
                        return { results: data.result }
                    }
                },
                formatSelection: function(data) { 
                    return data.text; 
                },
                formatNoMatches: function(term) {
                    return GlizyLocale.EntityFormEditEntitySelect.noEntitiesFound;    
                },
            });
            
            var value = jQuery(this).data('origValue');
            
            // relazione uno a molti
            if (cardinality == 1) {
                $(this).select2('data', value);
            }
            // relazione uno a uno
            else {
                $(this).select2('data', value[0]);
            }
		},

		getValue: function () {
            var value = $(this).select2('val');
            
            if (typeof value === 'string') {
                value = value === '' ? [] : [value];   
            }
            
            return value;
		},

		setValue: function (value) {
            if (value[0].id) {
                $(this).select2('data', value.length == 1 ? value[0] : value);
            }
		},

		destroy: function () {
		}
	});
}