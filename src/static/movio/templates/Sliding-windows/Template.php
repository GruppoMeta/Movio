<?php
class Template extends movio_views_AbstractLessTemplate
{
	function __construct()
	{
		$this->path = __DIR__;
		$this->templateName = 'Sliding-windows';
	}

	protected function applyCssVariables(&$templateData, $less, $css)
	{
		$variables = array();
		foreach ($templateData as $k=>$v) {
			if (strpos($k, 'c-')===0) {
				if (strlen($v)==0) {
					$v = 'transparent';
				}
				else if (strlen($v)!=7) {
					$v = '#000000';
				}
				$variables[$k] = $v;
			}
		}
		$less->setVariables($variables);

		if ($templateData->{'c-body-background'})
		{
			$css .= <<<EOD
body {
   background: @c-body-background;
}
EOD;
		}

		$css .= <<<EOD

@box-header-background: @c-box-header-background;
@text: @c-text;
@text-heading: @c-text-heading;
@color-link: @c-color-link;
@color-link-hover: darken(@c-color-link, 5);
@breadkcrumbs-link: @c-breadkcrumbs-link;
@breadkcrumbs-link-hover: @color-link-hover;

@slider-background: @c-slider-background;
@slider-text: @c-slider-text;
@slider-bullet-background: @c-slider-bullet-background;
@slider-bullet-background-selected: @c-slider-background-selected;

@sidebar-background: @c-sidebar-background;
@sidebar-background-hover: @c-sidebar-background-hover;
@sidebar-border: @c-sidebar-border;
@sidebar-link: @c-color-link;
@sidebar-link-hover: @c-color-link-hover;

@icon-in-page: lighten(@c-text, 70);
@icon-in-box: @c-icon-in-box;
@icon-in-box-background: @c-icon-in-box-background;

@box-image-border: @c-box-border;
@box-background: @c-box-background;
@box-border: @c-box-border;
@box-header-link: @c-box-header-link;
@box-relation-item-border: @c-box-border;
@box-text: @c-box-text;

@color-border-button: @c-color-border-button;
@color-arrow-button-slider: @c-color-arrow-button-slider;
@color-arrow-button-slider-hover: @c-color-arrow-button-slider-hover;
@color-background-button: @c-color-background-button;

@storyteller-background: @c-storyteller-background;
@storyteller-border: darken(@c-storyteller-background, 5);
@storyteller-icon: lighten(@c-text, 70);
@storyteller-border: @c-storyteller-border;
@storyteller-item-background: @c-storyteller-item-background;
@storyteller-comments-background: @c-storyteller-item-background;
@storyteller-link: @c-storyteller-link;
@storyteller-navigation-link: @c-storyteller-navigation-link;
@storyteller-image-border: @c-storyteller-image-border;

@form-border: @c-form-border;
@form-required: @c-form-border;
@form-button: @c-form-button;
@form-button-text: @c-form-button-text;
@form-button-primary: @c-form-button-primary;
@form-button-primary-text: @c-form-button-text;
@form-input-text: @c-text;
@form-input-background:@c-form-input-background;

@timeline-theme: @c-timeline-theme;

@footer-background: @c-footer-background;
@footer-border:@c-footer-border;
@footer-text: @c-footer-text;
@footer-text-link: @c-color-link;
@background-info-page: @c-background-info-page;

@color-link-menu: @c-color-link-menu;
@border-color-link: @c-border-color-link;
@border-sub-title-page: @c-border-sub-title-page;
@border-input-header: @c-border-input-header;
@color-link-enity: @c-color-link-entity;
@button-zoom-img: @c-button-zoom-img;
@box-sub-menu-background: @c-box-sub-menu-background;
@background-main-content: @c-background-main-content;

EOD;

		return $css;
	}


	protected function applyFont(&$templateData, $css)
	{
		$font1 = $templateData->font1 == 'default' ? 'PT Sans' : $templateData->font1;
		$font2 = $templateData->font2 == 'default' ? 'PT Serif' : $templateData->font2;

		$fonts = '@import url(http://fonts.googleapis.com/css?family='.str_replace(' ', '+', $font1).');'.PHP_EOL.
				 '@font-1: \''.$font1.'\', sans-serif;'.PHP_EOL.
				 '@import url(http://fonts.googleapis.com/css?family='.str_replace(' ', '+', $font2).');'.PHP_EOL.
			     '@font-2: \''.$font2.'\', sans-serif;'.PHP_EOL;
		return $css.PHP_EOL.$fonts;
	}


	protected function addLogoCss(&$templateData, $css)
	{
		$templateData->headerLogo = @json_decode($templateData->headerLogo);
		if ($templateData->headerLogo) {
			$image = org_glizycms_mediaArchive_MediaManager::getMediaById($templateData->headerLogo->id);
			$fileName = $image->getFileName();

			$templateData->customCss .= <<<EOD
header .site-logo {
    background: url("../{$fileName}") no-repeat ;
}
EOD;
		}

		return $css;
	}

	protected function addCustomCss(&$templateData, $css)
	{
		$css .= PHP_EOL.$templateData->customCss;
		return $css;
	}

	protected function addCustomOutput(&$view, &$templateData)
	{
	}

	public function fixTemplateData(&$templateData, &$newTemplateData)
	{
 		$templateData->footerLogo = @json_decode($templateData->footerLogo);
        if ($templateData->footerLogo && $templateData->footerLogo->id) {
            $image = org_glizy_helpers_Media::getImageById($templateData->footerLogo->id);
            if ($templateData->footerLogoLink) {
                $image = __Link::formatLink($templateData->footerLogoLink, $templateData->footerLogoTitle, $image);
            }
            $newTemplateData->footerLogo = $image;
        }
	}

	protected function fixTemplateName($view)
	{
		$view->setAttribute('templateFileName', 'page.php');
	}


	public static function homeSliderImage($item) {
	    return 'background:url('.$item->image['src'].') no-repeat 0 0;';
	}
}