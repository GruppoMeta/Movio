Glizy.oop.declare("glizy.FormEdit.checkbox", {
    $extends: Glizy.oop.get('glizy.FormEdit.standard'),

    getValue: function () {
		return this.$element.is(':checked');
    },

    setValue: function (value) {
        this.$element[0].checked = (value===true || value==='true' || value==='1' || value===1);
    },
});