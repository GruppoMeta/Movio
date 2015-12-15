jQuery.GlizyRegisterType('selectpagetype', {

	__construct: function () {
        var $that = $(this);
        var $previous = null;
        var linkedElement = $that.data('linked');
        var onlyWithParent = $that.data('onlywithparent')===true;
        var isSelect = linkedElement && $('#'+linkedElement)[0].tagName=='SELECT';

        $that.next().hide();

        var updateItemVisibility = function() {
            var currentMenuType = isSelect ? $('#'+linkedElement+' option:selected').data('options') :
                                             $('#'+linkedElement).data('options');
            if (!currentMenuType) {
                currentMenuType = $('#'+linkedElement).val();
            }
            $that.next().find('a').each(function(index, el) {
                var $el = $(el),
                    acceptType = $el.data('acceptparent');
                $el.parent().show();
                if (currentMenuType && (acceptType || (!acceptType && onlyWithParent))) {
                    acceptType = acceptType.split(',');
                    if (acceptType.indexOf(currentMenuType)==-1) {
                        $el.parent().hide();
                        if ($el.parent().hasClass('button-selected')) {
                            $el.parent().removeClass('button-selected');
                            $that.val('');
                        }
                    }
                }
            });
        }

        $(this).next().find('a').click(function( event ) {
            $that.next().find('li').each(function( index ) {
                $(this).removeClass('button-selected');
            });
            $(this).parent().addClass('button-selected');
            var pageType = $(this).data('type');
            $that.val(pageType);
        });

        var currentValue = $that.val();
        if (currentValue) {
            $(this).next().find('a').each(function(index) {
                if ($(this).data('type')==currentValue) {
                    $(this).parent().addClass('button-selected');
                }
            });
        }

        if (linkedElement) {
            $('#'+linkedElement).change(function(){
                updateItemVisibility();
            });

            updateItemVisibility();
        }

        $that.next().show();


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