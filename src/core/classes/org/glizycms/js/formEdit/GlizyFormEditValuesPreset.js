jQuery.GlizyRegisterType('valuesPreset', {
    __construct: function () {
        var self = $(this).data('formEdit');
        self.element = $(this);
        self.element.on('change', function() {
            var options = self.element.find('option:selected').data('options');
            var elements = self.element.data('elements');
            if (options && elements) {
                options = options.split(',');
                elements = elements.split(',');
                $(elements).each(function(index, item){
                    $('input[name='+item+']').val(options[index]).change();
                });
            }
        });

    },

    getValue: function () {
        return $(this).val();
    },

    setValue: function (value) {
        $(this).val(value);
    },

    destroy: function () {
    }
});
