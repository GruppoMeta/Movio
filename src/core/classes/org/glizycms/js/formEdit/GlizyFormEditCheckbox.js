jQuery.GlizyRegisterType('checkbox', {

	__construct: function () {
        var self = this;
        var format = $(this).data('format');
        var group = $(this).data('group');
        if (format) {
            format = format.split('/');
        } else {
            format = [0, 1];
        }

        if (group && $(this).attr('name').match(/\[\w*\]/)) {
            group += $(this).attr('name').replace(/\[\w*\]/, '');
        }

        $(this).on('change', function(e){
            self._getValue();
            if (group) {
                Glizy.events.broadcast("glizycms.checkbox.group", {group: group, checked: $(self).attr('name') });
            }
        });

        this._setValue = function(v) {
            if (v===undefined) {
                var value = $(this).data('origValue');
                var currentValue = $(this).val();
                if (currentValue=='true') {
                    currentValue = '1';
                }
                if (value!=='' && value!== undefined && value!=currentValue) {
                    value = currentValue;
                    jQuery(this).data('origValue', value);
                }
            } else {
                value = v;
            }

            $(this).attr('checked', value == format[1]);
        }

        this._getValue = function() {
            var value = format[ $(this)[0].checked ? 1 : 0];
            $(this).data('origValue', value);
            $(this).val(value);
            return value;
        }

        if (group) {
            Glizy.events.on("glizycms.checkbox.group", function(e){
                if (group==e.message.group) {
                    $(self)[0].checked = $(self).attr('name')==e.message.checked;
                    self._getValue();
                }
            });
        }

        this._setValue();
	},

    getValue: function () {
        return this._getValue();
    },

    setValue: function (value) {
        this._setValue(value);
    },

	destroy: function () {
        $(this).off("change");
	}
});