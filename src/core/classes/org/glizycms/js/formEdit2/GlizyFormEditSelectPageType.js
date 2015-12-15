Glizy.oop.declare("glizy.FormEdit.selectpagetype", {
    $extends: Glizy.oop.get('glizy.FormEdit.standard'),
    
    initialize: function (element) {
        element.data('instance', this);
        this.$element = element;
        var $previous = null;

        element.next().find('a').click(function( event ) {
            element.next().find('li').each(function( index ) {
                $(this).removeClass('button-selected');
            });
            $(this).parent().addClass('button-selected');
            var pageType = $(this).data('type');
            element.val(pageType);
        });

        var currentValue = element.val();
        if (currentValue) {
            element.next().find('a').each(function(element) {
                if ($(this).data('type')==currentValue) {
                    $(this).parent().addClass('button-selected');
                }
            });
        }
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