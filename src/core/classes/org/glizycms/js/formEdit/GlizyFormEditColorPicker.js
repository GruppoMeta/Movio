jQuery.GlizyRegisterType('colorPicker', {
    __construct: function () {
        var self = $(this).data('formEdit');
        self.element = $(this);

        self.element.css('width', 150);
        self.element.after('<div style="width: 28px; height:28px; float: left; border: 1px solid #e0e0e0;background-color:'+self.element.val()+'"></div>');

        self.element.colorpicker().on('changeColor', function(ev){
            var el = $(ev.currentTarget);
            el.next().css('background-color', ev.color.toHex());
        });

        self.element.on('change', function(e) {
            var el = $(e.currentTarget);
            el.next().css('background-color', el.val());
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
