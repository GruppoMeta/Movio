Glizy.oop.declare("glizy.FormEdit.checkbox", {
    $extends: Glizy.oop.get('glizy.FormEdit.standard'),
    
    getValue: function () {
       return this.$element.attr('checked') ? 1 : 0;
    },
    
    setValue: function (value) {
        this.$element.attr('checked', value == 1);
    },
});