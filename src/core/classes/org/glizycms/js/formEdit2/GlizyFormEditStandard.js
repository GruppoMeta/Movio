Glizy.oop.declare("glizy.FormEdit.standard", {
    $element: null,

    initialize: function (element) {
        element.data('instance', this);
        this.$element = element;
    },

    getElement: function () {
        return this.$element;
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
    },

    isValid: function() {
        if (this.$element.hasClass('required')) {
            return this.getValue() != '';
        } else {
            return true;
        }
    }
});