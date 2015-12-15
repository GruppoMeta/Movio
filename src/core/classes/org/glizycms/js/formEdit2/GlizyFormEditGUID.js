Glizy.oop.declare("glizy.FormEdit.inputguid", {
    $extends: Glizy.oop.get('glizy.FormEdit.standard'),
  
    initialize: function (element) {
        element.data('instance', this);
        this.$element = element;
        if (element.val() == '') {
            this.$element.val(this.$element.data('base')+(new Date().getTime()));
        }
    },
    
    getValue: function () {
        return this.$element.val();
    },
    
    setValue: function (value) {
        if (value != undefined) {
            this.$element.val(value);
        }
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