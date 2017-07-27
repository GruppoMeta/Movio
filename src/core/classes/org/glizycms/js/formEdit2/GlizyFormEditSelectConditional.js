Glizy.oop.declare("glizy.FormEdit.selectConditional", {
    $extends: Glizy.oop.get('glizy.FormEdit.standard'),
    target: null,

    initialize: function (element) {
        this.$super(element);
        var that = this;
        var lastSelected = null;
        this.target = element.data('target');

        $.each(that.target, function(i, value) {
            that.target[i] = value.split(',');
            $.each(that.target[i], function(i, value) {
                that.hideComponent(value);
            });
        });

        element.on('change', function(){
            if (lastSelected !== null && that.target[lastSelected]) {
                that.hideTarget(lastSelected);
            }

            var i = $(this).prop("selectedIndex");

            if (that.target[i]) {
                that.showTarget(i);
            }

            lastSelected = i;
        });

        element.trigger('change');
    },

    hideTarget: function (i) {
        var that = this;
        $.each(that.target[i], function(i, value) {
            that.hideComponent(value);
        });
    },

    showTarget: function (i) {
        var that = this;
        $.each(that.target[i], function(i, value) {
            that.showComponent(value);
        });
    },

    hideComponent: function (componentId) {
        $('#'+componentId).hide();
        $('#'+componentId).parents('div.form-group').hide();
    },

    showComponent: function (componentId) {
        console.log('show '+componentId);
        $('#'+componentId).show();
        $('#'+componentId).parents('div.form-group').show();
    }
});
