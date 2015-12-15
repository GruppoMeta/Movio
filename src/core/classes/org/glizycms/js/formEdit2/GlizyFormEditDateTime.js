Glizy.oop.declare("glizy.FormEdit.datetime", {
    $extends: Glizy.oop.get('glizy.FormEdit.standard'),
  
    initialize: function (element) {
        element.data('instance', this);
        this.$element = element;
        var format = element.data('format') ? element.data('format') : GlizyLocale.datetime.format;
       
        this.$element.datetimepicker({
            language: 'it',
            format: format,
            autoclose: true,
            todayHighlight: true
        });
    },
    
    getValue: function () {
        return this.$element.val();
    },
    
    setValue: function (value) {
        this.$element.val(value);
        this.$element.datetimepicker('update');
    },
    
    getName: function () {
        return this.$element.attr('name');
    },
    
    focus: function () {
        document.getElementById(this.$element.attr('id')).scrollIntoView();
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