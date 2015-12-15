jQuery.GlizyRegisterType('inputguid', {
    __construct: function () {
        var self = $(this).data('formEdit');
        self.element = $(this);
        if (''==self.element.val()) {
            self.element.val(self.element.data('base')+(new Date().getTime()));
        }
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
