$(window).load(function() {
    Movio.init();

    $(".menu").jqxMenu({});

    // TOOLTIP
    $(function() {
        $("[rel='tooltip']").tooltip();
        $('#element').tooltip('show')
    });

	/* SBLOCCA PER ATTIVARE */
	// scrollbar documentation http://manos.malihu.gr/jquery-custom-content-scroller/
	$(".overflow").mCustomScrollbar({
		scrollButtons:{
			enable:true
		},
		mouseWheel: false,
	   advanced:{
			updateOnContentResize: true
		}
	});

	// PLACEOLDER
	//Assign to those input elements that have 'placeholder' attribute
	if($.browser.msie){
	   $('input[placeholder]').each(function(){

			var input = $(this);

			$(input).val(input.attr('placeholder'));

			$(input).focus(function(){
				 if (input.val() == input.attr('placeholder')) {
					 input.val('');
				 }
			});

			$(input).blur(function(){
				if (input.val() == '' || input.val() == input.attr('placeholder')) {
					input.val(input.attr('placeholder'));
				}
			});
		});
	};

	// ADD HEIGHT MAIN CONTENT
	var width1 = $(window).width();
	if(width1 >= 700) {
	function resize() {
		  var h = $(window).height() - $('#header').height() - $('#footer').height();
		  $('.main-content').height(h);
		  $('.slideshow .slide').height(h);
	  }

	  $(window).resize(function () {
		  resize();
	});

	  resize();
	}

    // VIDEO YOUTUBE LIQUID
    // Find all YouTube videos
    var $allVideos = $("iframe[src^='http://www.youtube.com']"),
    // The element that is fluid width
    $fluidEl = $(".item-slide-height-full");
    // Figure out and save aspect ratio for each video
    $allVideos.each(function() {
        $(this)
            .data('aspectRatio', this.width / (this.height))
            .removeAttr('height')
            .removeAttr('width')
    });

    // When the window is resized
    // (You'll probably want to debounce this)
    $(window).resize(function() {
        var newHeight = $fluidEl.height() -20;
        // Resize all videos according to their own aspect ratio
        $allVideos.each(function() {
            var $el = $(this);
            $el
                .height(newHeight)
                .width(newHeight * $el.data('aspectRatio'));
        });
        // Kick off one resize to fix all videos on page load
    }).resize();
});
