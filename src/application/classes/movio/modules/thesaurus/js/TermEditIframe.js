Glizy.module('thesaurus.TermEditIframe', function(){
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

        Glizy.events.on("thesaurus.termEdit", function(e) {
            self.changeUrl(e.message.termId);
        });

        Glizy.events.on("thesaurus.termAdd", function(e){
            self.changeUrl(null, e.message.href);
        });

        $('body').css('overflow', 'hidden');
        $(window).resize(Glizy.responder(this, this.onResize));
        this.onResize();
    };

    this.changeUrl = function(termId, href) {
        if (termId) {
            jQuery("#modalDiv").remove();
            this.iframe.attr("src", this.editSrc+termId+"&_"+(new Date()).getTime());
        } else {
            this.iframe.attr("src", href);
        }
    };

    this.onResize = function() {
        var h = $(window).height() - this.iframeOffset.top;
        this.iframe.height(h);
    };
});
