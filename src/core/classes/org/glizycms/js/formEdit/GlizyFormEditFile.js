jQuery.GlizyRegisterType('file', {
    __construct: function () {
        var self = this;
        self.el = $(this);
        self.el.hide();
        var isWaiting = false;
        var queue = [];
        var uploadedFileCount = 0;

         self.init = function() {
            var maxFiles = self.el.data('maxfiles');
            var maxFilesize = self.el.data('maxfilesize') || 256;
            var acceptedFiles = self.el.data('acceptedfiles');
            var dropId = self.el.attr('id')+'dropDiv';
            self.dropDiv = jQuery('<div id="'+dropId+'" class="glizy-dropzone"><p>'+GlizyLocale.FineUploader.uploadButton+' (' + GlizyLocale.FineUploader.maxsize + ': '+self.el.data('maxlabel')+')'+'</p></div>').insertAfter(self.el);
            var myDropzone = new Dropzone("div#"+dropId, {
                            url: "uploader.php",
                            maxFiles: maxFiles,
                            maxFilesize: maxFilesize,
                            acceptedFiles: acceptedFiles
                        });
            myDropzone.on("addedfile", function(file) {
                self.dropDiv.find('p').hide();
                uploadedFileCount++;
                file.__id = uploadedFileCount;
                file.__status = 0;
                file.__previewId = 'fileUpload-'+file.__id;
                var preview = self.dropDiv.find('.dz-preview:last');
                preview.attr('id', file.__previewId);
            });
            myDropzone.on("success", function(file, response) {
                response.preview = file.__previewId;
                response.targetId = self.el.data('fieldsetid');
                self.dispatchDelayed(response);
            });
        };


        self.dispatchDelayed = function(info) {
            if (!isWaiting) {
                isWaiting = true;
                setTimeout(function () {
                    isWaiting = false;
                    Glizy.events.broadcast("glizycms.fileUpload", info);
                    if ( queue.length) {
                        info = queue.pop();
                        self.dispatchDelayed(info);
                    }
                }, 500);
            } else {
                queue.push(info);
            }
        }


         self.init();
    },

    destroy: function () {
    }
});

