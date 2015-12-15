Glizy.oop.declare("glizy.FormEdit.colorPicker", {
    $element: null,

    initialize: function (element, data) {
        element.data('instance', this);
        this.$element = element;

        element.css('width', 300);
        element.after('<div style="width: 28px; height:28px; float: left; border: 1px solid #e0e0e0;background-color:'+element.val()+'"></div>');

        element.colorpicker().on('changeColor', function(ev){
            var el = $(ev.currentTarget);
            el.next().css('background-color', ev.color.toHex());
        });

        element.on('change', function(e) {
            var el = $(e.currentTarget);
            el.next().css('background-color', el.val());
        });
    },

    getValue: function () {
        return this.$element.val();
    },

    setValue: function (value) {
        this.$element.val(value);
    },

    getName: function () {
        return this.$element.attr('name');
    },

    focus: function()
    {
        this.$element.focus();
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