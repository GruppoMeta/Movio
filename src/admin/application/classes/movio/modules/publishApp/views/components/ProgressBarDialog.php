<?php
class movio_modules_publishApp_views_components_ProgressBarDialog extends org_glizy_components_Component
{
	function render($outputMode = NULL, $skipChilds = false)
	{
	    $this->addOutputCode( org_glizy_helpers_CSS::linkCSSfile( __Paths::get('APPLICATION_CLASSES').'movio/modules/publishApp/static/progressBar.css' ), 'head' );

  		$output = <<<EOD
<script type="text/javascript">
$(function() {
  var state = 'progress';

  (function ($) {
  	$.fn.animateProgress = function (progress, callback) {
			jQuery.fx.off = false;
			//var myduration = (progress - old_progress) * 1000 * (durata_totale_stimata / 100);
			old_progress = progress;
			return this.each(function () {
				$(this).animate({
					width: String(progress) + '%'
				}, {
					//duration: myduration,

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
</script>
EOD;

    	$output .= '<div id="progress_bar" class="ui-progress-bar ui-container">
                  <div class="ui-progress" style="width: 0%;">
                  <span class="ui-label" style="display:none;"><b class="value">0%</b></span>
		              </div></div>';
		$this->addOutputCode( $output );
	}
}
?>
