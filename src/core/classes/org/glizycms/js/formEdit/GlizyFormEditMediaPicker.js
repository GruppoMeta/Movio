jQuery.GlizyRegisterType('mediapicker', {
		__construct: function () {
			var $input = jQuery(this).hide(),
				val = $input.val(),
				pickerType = $input.attr('data-mediatype'),
				hasPreview = $input.attr('data-preview') == 'true',
				glizyOpt = $input.data('glizyOpt'),
				$mediaPicker =
					hasPreview ? jQuery('<div id="'+jQuery(this).attr('name')+'-mediapicker" class="mediaPickerSelector mediaPickerField"><div class="mediaPickerCaption"></div><div class="mediaPickerElement">' + GlizyLocale.MediaPicker.imageEmpty + '</div></div>')
					: jQuery('<input class="mediaPickerField" type="text" size="50" readonly="readonly" style="cursor:pointer" value="' + GlizyLocale.MediaPicker.imageEmptyText + '">'),
				readonly = $input.attr('readonly') == 'readonly';

			$input.data('mediaPicker', $mediaPicker);

			if (readonly) {
				$mediaPicker.insertAfter($input);
			} else if (!$input.next().hasClass('mediaPickerField')) {
				$mediaPicker.insertAfter($input).click(function() {
						var url = glizyOpt.mediaPicker;
						if (pickerType) {
							url += '&mediaType=' + pickerType;
						}
						Glizy.openIFrameDialog( hasPreview ? GlizyLocale.MediaPicker.imageTitle : GlizyLocale.MediaPicker.mediaTitle,
												url,
												1400,
												50,
												50,
												null,
												Glizy.responder($input, $input.data('formEdit').disposeEvent));
						Glizy.lastMediaPicker = jQuery(this);

				        var eventPos = Glizy.events.on("glizycms.onSetMediaPicker", Glizy.responder($input, $input.data('formEdit').onSetMediaPicker));
				        $input.data('eventPos', eventPos);
					});
			}
			if (val == GlizyLocale.MediaPicker.imageEmptyText) {
				$input.data('formEdit').setValue.call($mediaPicker);
			}
			else if (val) {
				$input.data('formEdit').setValue.call($mediaPicker, val);
			}
		},

		disposeEvent: function()
		{
			if (Glizy.lastMediaPicker) {
				var $this = Glizy.lastMediaPicker.prev();
				var eventPos = $this.data('eventPos');
				if (eventPos!==null && eventPos!==undefined) {
					Glizy.events.unbind("glizycms.onSetMediaPicker", eventPos);
					$this.data('eventPos', null);
				}
			}
		},

		onSetMediaPicker: function(event)
		{
			var $this = Glizy.lastMediaPicker.prev();
			$this.data('formEdit').disposeEvent.call();
			$this.data('formEdit').setValue.call(Glizy.lastMediaPicker, event.message);
			Glizy.closeIFrameDialog();
            var mediaPickerId = $this.attr('name')+'-mediapicker';
            $('#'+mediaPickerId).removeClass('GFEValidationError');
		},

		getPreview: function (val) {
			try {
				props = JSON.parse(val);
				return props.title;
			} catch(e) {
				return val;
			}
		},

		setValue: function (props) {
			if (typeof(props)=='string' && props) {
				props = JSON.parse(props);
			}
			var $this = jQuery(this);
            if (!$this.prev().length) {
                return;
            }
			if ($this.data('mediaPicker')) {
				$this = $this.data('mediaPicker');
			}
			var	$img = $this.find('img');

			if (!props || !props.src) {
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

					var src = $this.prev().data('glizyOpt').imageResizer.replace('#id#', props.id);
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

		destroy: function () {
			var $this = jQuery(this);
			$this.data('formEdit').disposeEvent.call();
		},

        focus: function () {
            var mediaPickerId = jQuery(this).attr('name')+'-mediapicker';
            $('#'+mediaPickerId).addClass('GFEValidationError');
            document.getElementById(mediaPickerId).scrollIntoView();
        }

	});
