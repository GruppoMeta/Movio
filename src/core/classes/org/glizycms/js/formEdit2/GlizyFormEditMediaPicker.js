Glizy.oop.declare("glizy.FormEdit.mediapicker", {
    $extends: Glizy.oop.get('glizy.FormEdit.standard'),
    $mediaPicker: null,
    populateDataEnabled: false,
    eventPos: null,
    imageResizer: null,

    initialize: function (element, glizyOpt) {
        element.data('instance', this);
        this.$element = element;
        this.populateDataEnabled = element.attr('data-populate_data') == 'true';
        this.imageResizer = glizyOpt.imageResizer;

        var that = this;
        var $input = element.hide(),
            pickerType = $input.attr('data-mediatype'),
            externalFiltersOR = $input.attr('data-externalfiltersor'),
            hasPreview = $input.attr('data-preview') == 'true';

        that.$mediaPicker =
            hasPreview ? jQuery('<div id="'+element.attr('name')+'-mediapicker" class="mediaPickerSelector mediaPickerField"><div class="mediaPickerCaption"></div><div class="mediaPickerElement">' + GlizyLocale.MediaPicker.imageEmpty + '</div></div>')
            : jQuery('<input class="mediaPickerField" type="text" size="50" readonly="readonly" style="cursor:pointer" value="' + GlizyLocale.MediaPicker.imageEmptyText + '">');

        if (!$input.next().hasClass('mediaPickerField')) {
            that.$mediaPicker.insertAfter($input).click(function() {
                    var url = glizyOpt.mediaPicker;
                    if (pickerType) {
                        url += '&mediaType=' + pickerType;
                    }
                    else if(externalFiltersOR){
                        url += '&externalFiltersOR=' + externalFiltersOR;
                    }
                    Glizy.openIFrameDialog( hasPreview ? GlizyLocale.MediaPicker.imageTitle : GlizyLocale.MediaPicker.mediaTitle,
                                            url,
                                            1400,
                                            50,
                                            50,
                                            null,
                                            Glizy.responder(that, that.disposeEvent));
                    Glizy.lastMediaPicker = that;
                    that.eventPos = Glizy.events.on("glizycms.onSetMediaPicker", Glizy.responder(that, that.onSetMediaPicker));
                });
        }

    },

    getValue: function () {
        return this.$element.val();
    },

    setValue: function (value) {
        if (value) {
            this.setProps(JSON.parse(value));
        }
    },

    populateData: function(values) {
        // TODO: slegare il componente dal repeater
        var $container = this.$element.closest('.GFERowContainer');

        for (var field in values) {
            var $el = $container.find('input[data-media_picker_mapping='+field+']');
            if ($el) {
                var obj = $el.data('instance');

                if (obj) {
                    obj.setValue(values[field]);
                }
            }
        }
    },

    clearData: function() {
        // TODO: slegare il componente dal repeater
        var $container = this.$element.closest('.GFERowContainer');
        $container.find('input[disabled=disabled]').val('');
    },

    setProps: function (props) {
        var $this = this.$mediaPicker,
            $img = $this.find('img');

        if (this.populateDataEnabled) {
            if (props) {
                this.populateData(props);
            } else {
                this.clearData();
            }
        }

        if (!props || !props.id) {
            if ($img.length) {
                $img.replaceWith(GlizyLocale.MediaPicker.imageEmpty);
            }
            else {
                $this.val(GlizyLocale.MediaPicker.imageEmptyText);
            }
            $this.prev().val('');
        }
        else {
            if ($img.length) {
                $img.load(function () {

                        var w = this.naturalWidth,
                            h = this.naturalHeight,
                            maxW = $this.width() -6,
                            maxH = $this.height() -6;

                        if (w > maxW) {
                            h = h * (maxW / w);
                            w = maxW;
                        }
                        if (h > maxH) {
                            w = w * (maxH / h);
                            h = maxH;
                        }
                        jQuery(this).attr({width: w, height: h})
                            .show();
                    })
                    .hide();

                var src = this.imageResizer.replace('#id#', props.id);
                $img.attr({title: props.title, src: src})
                    .data({id: props.id, fileName: props.fileName});

                if ($img[0].complete && $img[0].naturalWidth !== 0) {
                    $img.trigger('load');
                }
            }
            else {
                $this.val(props.title);
            }
            $this.prev().val( JSON.stringify(props) );
        }
    },

    getName: function () {
        return this.$element.attr('name');
    },

    getPreview: function (val) {
        try {
            var props = JSON.parse(val);
            return props.title;
        } catch(e) {
            return val;
        }
    },

    disposeEvent: function()
    {
        if (this.eventPos!==null && this.eventPos!==undefined) {
            Glizy.events.unbind("glizycms.onSetMediaPicker", this.eventPos);
            this.eventPos = null;
        }
    },

    onSetMediaPicker: function(event)
    {
        this.disposeEvent();
        this.setProps(event.message);
        Glizy.closeIFrameDialog();
    },

    focus: function () {
        var mediaPickerId = this.$element.attr('id')+'-mediapicker';
        $('#'+mediaPickerId).addClass('GFEValidationError');
        document.getElementById(mediaPickerId).scrollIntoView();
    },

    destroy: function() {
        this.disposeEvent();
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
