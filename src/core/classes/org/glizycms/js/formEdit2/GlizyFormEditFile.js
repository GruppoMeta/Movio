Glizy.oop.declare("glizy.FormEdit.file", {
    $extends: Glizy.oop.get('glizy.FormEdit.standard'),
    isWaiting: false,
    alertSent: false,
    queue: [],
    uploadedFiles: [],

    initialize: function (element) {
        element.data('instance', this);
        this.$element = element;
        var isWaiting = false;
        var alertSent = false;
        var that = this;

		var uploadedFiles = [],
			$input = element.hide(),
			$uploader = jQuery('<div></div>').insertAfter($input);

		$uploader.fineUploader({
			request: {
				endpoint: 'uploader.php'
			},
			text: GlizyLocale.FineUploader
		})
		.bind('complete', function(event, id, fileName, responseJSON){
            if (responseJSON.uploadFilename) {
				that.uploadedFiles.push( [responseJSON.uploadFilename, responseJSON.originFilename] );
				$input.val( JSON.stringify( that.uploadedFiles ) );
				jQuery("li.qq-upload-success:not(:has(.fu-remove-button))").append('<div class="fu-remove-button"></div>');
                that.dispatchDelayed({"id": id, 'fileName': fileName});
            }
            else {
                if (!that.alertSent) {
                    alert(responseJSON.error); 
                    that.alertSent = true;
                }
            }
		})
        .bind('upload', function(event, id, fileName) {
            
		})

		jQuery(document).on('click', '.fu-remove-button', function () {

			var $this = element,
				filename = $this.siblings('.qq-upload-file').text(),
				i, f;

			for (i = 0; f = that.uploadedFiles[i]; i++) {
				if (f[1] == filename) {
					that.uploadedFiles.splice(i, 1);
					break;
				}
			}
			$input.val( JSON.stringify( that.uploadedFiles ) );
			$this.parent().remove();
            Glizy.events.broadcast("glizycms.fileRemoved", i);
		});
    },
    
    getValue: function () {
        return this.$element.val();
    },
    
    setValue: function (value) {
        this.$element.val(value);
    },
    
    getName: function () {
        return this.$element.attr('name');
    },
    
    removeFile: function (id) {
        this.uploadedFiles = JSON.parse(this.$element.val());
        this.uploadedFiles.splice(id, 1);
        this.$element.val( JSON.stringify( this.uploadedFiles ) );
        this.$element.siblings().find('.qq-upload-success').get(id).remove();
    },
    
    dispatchDelayed: function(info) {
        var that = this;
        
        if (!that.isWaiting) {
            that.isWaiting = true;
            setTimeout(function () {
                that.isWaiting = false;
                Glizy.events.broadcast("glizycms.fileUpload", info);
                if ( that.queue.length) {
                    info = that.queue.pop();
                    that.dispatchDelayed(info);
                }
            }, 500);
        } else {
            this.queue.push(info);
        }
    },
    
    focus: function()
    {
        this.$element.focus();
    },
    
    destroy: function() {
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