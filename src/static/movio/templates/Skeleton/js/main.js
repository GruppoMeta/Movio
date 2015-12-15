$(window).load(function() {
    // TOOLTIP
    $(function() {
        $("[rel='tooltip']").tooltip();
        $('#element').tooltip('show')
    });


    // VIDEO YOUTUBE LIQUID
    // Find all YouTube videos
    var $allVideos = $("iframe[src^='http://www.youtube.com']"),
    // The element that is fluid width
    $fluidEl = $(".item-box");
    // Figure out and save aspect ratio for each video
    $allVideos.each(function() {
        $(this)
            .data('aspectRatio', this.height / this.width)
            .removeAttr('height')
            .removeAttr('width');
    });

    // When the window is resized
    // (You'll probably want to debounce this)
    $(window).resize(function() {
        var newWidth = $fluidEl.width();
        // Resize all videos according to their own aspect ratio
        $allVideos.each(function() {
            var $el = $(this);
            $el
                .width(newWidth)
                .height(newWidth * $el.data('aspectRatio'));

        });
        // Kick off one resize to fix all videos on page load
    }).resize();
});
