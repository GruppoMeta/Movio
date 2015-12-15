var Movio = {
    init: function() {
      Movio.initImageSlider('.js-imageList');
      Movio.initTooltip();
      Movio.initThesaurus();
      Movio.fixPlaceholderInOldBrowser();
      Movio.fixVideoResize();
    },

    initImageSlider: function(sel) {
        $(sel+' .slide').each(
            function(index) {
                $(this).css( "z-index", -index*2+2 );
            }
        );

        var funInitSlick = function(sel) {
            $(sel).slick({
              dots: false,
              infinite: false,
              speed: 700,
              slidesToShow: 6,
              slidesToScroll: 1,
              responsive: [{
                  breakpoint: 1024,
                  settings: {
                      slidesToShow: 3,
                      slidesToScroll: 1,
                      infinite: true,
                      dots: false
                  }
              }, {
                  breakpoint: 600,
                  settings: {
                      slidesToShow: 2,
                      slidesToScroll: 1
                  }
              }, {
                  breakpoint: 480,
                  settings: {
                      slidesToShow: 2,
                      slidesToScroll: 1
                  }
              }]
          });
        };

        var $sel = $(sel),
            $collapse = $sel.closest('div.collapse');

        if ($collapse.length==0 || $collapse.hasClass('in')) {
            funInitSlick(sel);
        } else {
            $collapse.on('show.bs.collapse', function () {
                $collapse.off('show.bs.collapse');
                $(sel).css({'height': '10px', 'overflow': 'hidden'});
                setTimeout(function(){
                    funInitSlick(sel);
                    $(sel).css({'height': 'auto', 'overflow': 'auto'});
                }, 50)
            })
        }
    },

    initTooltip: function() {
      $("[rel='tooltip']").tooltip();
      $('#element').tooltip('show')
    },

    initThesaurus: function() {
      $('a.js-thesaurus').click(function(e){
        e.preventDefault();
        e.stopPropagation();

        // show popup
        var $el = $(this),
          $body = $('body'),
          offset = $el.position(),
          popup = $('.js-thesaurus-popup'),
          popupTitle = $('.js-thesaurus-title'),
          popupResult = $('.js-thesaurus-result');

        popupTitle.html($el.html());
        popupResult.html('loading ...');
        popup.css({ position: 'absolute',
                    'z-index': 2000,
                    top: offset.top + 30,
                    left: offset.left - popup.width() + 30 + ($el.width()/2)});
        popup.fadeIn();
        $body.animate({scrollTop: $body.scrollTop() + $el.offset().top - 10}, 400);

        var clickHandler = function() {
          popup.fadeOut();
          $(document).unbind('click', clickHandler);
        }
        $(document).bind('click', clickHandler);

        $.ajax({
                url: Glizy.ajaxUrl+$el.data('url'),
                success: function(data) {
                   popupResult.html(data);
                }
        });

      });
    },

    fixPlaceholderInOldBrowser: function() {
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
  },

  fixVideoResize: function() {
    $(window).load(function(){
      // VIDEO YOUTUBE LIQUID
      // Find all YouTube videos
      var $allVideos = $(".item-box iframe[src^='http://www.youtube.com']"),

        // The element that is fluid width
        $fluidEl = $(".item-box");

      // Figure out and save aspect ratio for each video
      $allVideos.each(function() {

        $(this)
          .data('aspectRatio', this.height / this.width)

          // and remove the hard coded width/height
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
  }
};