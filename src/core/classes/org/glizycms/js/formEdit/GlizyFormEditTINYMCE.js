jQuery.GlizyRegisterType('tinymce', {

		__construct: function () {

			var $this = jQuery(this),
				$container = $this.closest('.GFERowContainer'),
				$fieldSet = $container.parent(),
				options = Glizy.tinyMCE_options,
				h,
				readonly = $this.attr('readonly') == 'readonly';

		 	if (readonly) {
		 		$this.replaceWith('<div>'+$this.val()+'</div>');
		 	} else {
	        	options.mode = "exact";
				options.elements = this.name;
				options.document_base_url = Glizy.tinyMCE_options.urls.root;
				tinyMCE.init( options );

				if (!$fieldSet.attr('data-collapsable') == 'true') {
					h = $container.height();
					$container.height(h)
						.find('.GFERowHandler > img').attr('height', h);
				}
		 	}
		},

        save: function () {
            return tinyMCE.get(this.id).save();
        },

		getValue: function () {
			try {
				return tinyMCE.get(this.id).getContent();
			} catch (e) {
				// tinymce not ready
				return $(this).val();
			}

		},

		setValue: function (value) {

			tinyMCE.get(this.id).setContent(value || '');
		},

		destroy: function () {

			tinyMCE.execCommand('mceRemoveControl', true, this.id);
		}
	});
