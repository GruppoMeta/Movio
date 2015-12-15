$(function() {
  var state = 'progress';

  (function ($) {
    $.fn.animateProgress = function (progress, callback) {
      jQuery.fx.off = false;
      old_progress = progress;
      return this.each(function () {
        $(this).animate({
          width: String(progress) + '%'
        }, {
          easing: 'swing',
          step: function (progress) {
            if (state === 'progress') {

              jQuery.fx.off = false;

              var labelEl = $('.ui-label', this),
                valueEl = $('.value', labelEl);

              if (Math.ceil(progress) < 20) {
                labelEl.hide();
              } else {
                if (labelEl.is(":hidden")) {
                  labelEl.fadeIn();
                }
              }

            valueEl.text(Math.ceil(progress) + '%');
            }
          },

          complete: function (scope, i, elem) {
            if (callback) {
              scope = 0;
              callback.call(this, i, elem);
            }
          }
        });
      });
    };
  }(jQuery));


  var resetProgressBar = function () {
    jQuery.fx.off = true;
    $('#progress_bar .ui-progress').width("0%");
    $('#progress_bar .ui-label').hide();
  };

  resetProgressBar();
});