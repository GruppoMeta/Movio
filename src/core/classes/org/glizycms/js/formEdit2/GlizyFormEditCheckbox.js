Glizy.oop.declare("glizy.FormEdit.checkbox", {
    $extends: Glizy.oop.get('glizy.FormEdit.standard'),

    getValue: function () {
		return this.$element.is(':checked');
    },

    setValue: function (value) {
        if (value) {
            this.$element.attr('checked', value);
        }
    },
});