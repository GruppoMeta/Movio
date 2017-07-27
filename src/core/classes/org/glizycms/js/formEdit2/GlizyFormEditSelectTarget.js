Glizy.oop.declare("glizy.FormEdit.selectTarget", {
    $extends: Glizy.oop.get('glizy.FormEdit.standard'),

    initialize: function (element) {
        this.$super(element);

        var listId= element.data('list');
        var target = element.data('target').split(',');
        var index = element.data('index').split(',');
        var map = {};
        var $lastElementShown = null;

        $.each(index, function(i, value) {
            map[value] = i;
        });

        $.each(target, function(i, targetId) {
            element.find('#'+targetId).hide();
        });

        element.find('#'+listId).on('change', function(){
            var i = $(this).prop("selectedIndex");
            if ($lastElementShown) {
                $lastElementShown.hide();
            }
            $lastElementShown = element.find('#'+target[map[i]]);
            $lastElementShown.show();
        });
    },
});
