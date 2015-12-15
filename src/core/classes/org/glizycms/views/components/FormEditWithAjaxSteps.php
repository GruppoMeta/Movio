<?php
class org_glizycms_views_components_FormEditWithAjaxSteps extends org_glizycms_views_components_FormEdit
{
	public function render_html_onStart()
	{
        // TODO localizzare il javascript
        $this->addOutputCode(org_glizy_helpers_JS::linkCoreJSfile('jquery-simplemodal/jquery.simplemodal.1.4.1.min.js'));
        $this->addOutputCode(org_glizy_helpers_JS::linkCoreJSfile('progressBar/progressBar.js'));
        $this->addOutputCode(org_glizy_helpers_CSS::linkCoreCSSfile2('progressBar/progressBar.css'), 'head');
        $this->addOutputCode(org_glizy_helpers_JS::linkCoreJSfile('formWithAjaxSteps.js?v='.GLZ_CORE_VERSION));

        parent::render_html_onStart();

        $ajaxUrl = $this->getAttribute('controllerName') ? $this->getAjaxUrl() : '';

        $output = <<<EOD
<div id="progress_bar" class="js-glizycms-FormEditWithAjaxSteps ui-progress-bar ui-container" data-ajaxurl="$ajaxUrl">
  <div class="ui-progress" style="width: 0%;">
    <span class="ui-label" style="display:none;"><b class="value">0%</b></span>
  </div>
</div>
EOD;
        $this->addOutputCode($output);
	}
}
