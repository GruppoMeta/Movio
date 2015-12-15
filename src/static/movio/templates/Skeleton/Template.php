<?php
class Template extends movio_views_AbstractLessTemplate
{
	function __construct()
	{
		$this->path = __DIR__;
		$this->templateName = 'Skeleton';
	}

	protected function applyCssVariables(&$templateData, $less, $css)
	{
		return $css;
	}


	protected function applyFont(&$templateData, $css)
	{
		return $css;
	}


	protected function addLogoCss(&$templateData, $css)
	{
		return $css;
	}

	protected function addCustomCss(&$templateData, $css)
	{
		return $css;
	}

	public function fixTemplateData(&$templateData, &$newTemplateData)
	{
	}

	protected function fixTemplateName($view)
	{
		$view->setAttribute('templateFileName', 'page.php');
	}


	public static function homeSliderImage($item) {
	    return 'background:url('.$item->image['src'].') no-repeat 0 0;';
	}
}