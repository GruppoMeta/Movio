Glizy.module('cms.PageEditIframe', function(){
    this.iframe = null;
    this.emptySrc = null;
    this.editSrc = null;
    this.iframeOffset = null;

    this.run = function() {
        var self = this;
        this.iframe = $("#js-glizycmsPageEdit");
        this.iframeOffset = this.iframe.offset();
        this.emptySrc = this.iframe.data("emptysrc");
        this.editSrc = this.iframe.data("editsrc");
        this.changeUrl(null, this.emptySrc);

        Glizy.events.on("glizycms.pageEdit", function(e){
            self.changeUrl(e.message.menuId);
        });

        Glizy.events.on("glizycms.pageAdd", function(e){
            self.changeUrl(null, e.message.href);
        });

        $('body').css('overflow', 'hidden');
        $(window).resize(Glizy.responder(this, this.onResize));
        this.onResize();
    };

    this.changeUrl = function(menuId, href) {
        if (menuId) {
            jQuery("#modalDiv").remove();
            this.iframe.attr("src", this.editSrc+menuId+"&_"+(new Date()).getTime());
        } else {
            this.iframe.attr("src", href);
        }
    };

    this.onResize = function() {
        var h = $(window).height() - this.iframeOffset.top;
        this.iframe.height(h);
    };
});
