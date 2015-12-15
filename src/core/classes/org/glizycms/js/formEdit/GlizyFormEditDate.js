jQuery.GlizyRegisterType('date', {

	__construct: function () {
        var format = $(this).data('format') ? $(this).data('format') : GlizyLocale.date.format;
       
        $(this).datetimepicker({
            language: 'it',
            minView: 'month',
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
	},
    
    focus: function () {
        document.getElementById($(this).attr('id')).scrollIntoView();
    }
});