jQuery.GlizyRegisterType('datetime', {

	__construct: function () {
        var format = $(this).data('format') ? $(this).data('format') : GlizyLocale.datetime.format;
       
        $(this).datetimepicker({
            language: 'it',
            format: format,
            autoclose: true,
            todayHighlight: true
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