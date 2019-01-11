<?php
class Template extends GlizyObject
{
	const TEMPLATE_NAME = 'Minimal-in-blue';

	function render($application, $view, $templateData)
	{
		$templateData = $this->getTemplateDataFromCache($templateData);

		$siteProp = unserialize(org_glizy_Registry::get(__Config::get('REGISTRY_SITE_PROP').$view->_application->getLanguage(), ''));
		$view->addOutputCode($templateData->css, 'css');
		$view->addOutputCode($siteProp['title'], 'siteTitle');
		$view->addOutputCode($siteProp['subtitle'], 'siteSubtitle');
		if ($templateData->footerLogo) {
			$view->addOutputCode($templateData->footerLogo, 'logoFooter');
		}
        $view->setAttribute('templateFileName', 'page.php');
	}

	private function getTemplateDataFromCache($templateData)
	{
		$templateProxy = org_glizy_ObjectFactory::createObject('org.glizycms.template.models.proxy.TemplateProxy');
		// $templateProxy->invalidateCache();
        $cache = $templateProxy->getTemplateCache();

		$cssFileName = __Paths::get('CACHE').md5(__DIR__.'_'.$templateData->__id.__Config::get('APP_VERSION')).'.css';
        $self = $this;
		$templateData = $cache->get($cssFileName, array(), function() use ($self, $templateData, $cssFileName) {
			$newTemplateData = new StdClass;
			$newTemplateData->footerLogo = '';

			$self->updateTemplateData($templateData);
			$self->compileCss($templateData, $cssFileName);

			$templateData->footerLogo = @json_decode($templateData->footerLogo);
			if ($templateData->footerLogo && $templateData->footerLogo->id) {
				$image = org_glizy_helpers_Media::getImageById($templateData->footerLogo->id);
				if ($templateData->footerLogoLink) {
					$image = __Link::formatLink($templateData->footerLogoLink, $templateData->footerLogoTitle, $image);
				}
				$newTemplateData->footerLogo = $image;
			}

			$newTemplateData->css = $templateData->css;
			return $newTemplateData;
		});

		return $templateData;
	}

	public function updateTemplateData(&$templateData)
	{
		$elements = explode(',', 'c-body-background,c-text,c-text-heading,c-color-link,c-color-link-hover,c-box-image-border,c-navigation-background,c-sidebar-link,c-sidebar-link-hover,c-languages-link,c-languages-link-hover,c-metanavigation-link,c-metanavigation-link-hover,c-slider-background,c-slider-text,c-box-border,c-box-background,c-box-header-link,c-box-text,c-icon-in-box,c-icon-in-box-background,c-color-border-button,c-color-arrow-button-slider,c-color-arrow-button-slider-hover,c-form-border,c-form-required,c-form-input-text,c-form-input-background,c-form-button-primary,c-form-button,c-form-button-text,c-timeline-theme,c-storyteller-background,c-storyteller-item-background,c-storyteller-border,c-storyteller-navigation-link,c-footer-border,c-footer-text,c-svg-path-stroke,c-svg-node-border,c-svg-main-node-background,c-svg-text-link,c-svg-text-node,c-svg-text-main-node,c-svg-node-background');
		$colors = explode(',', '#FFFFFF,#333333,#333333,#0099FF,#008ae6,#CCCCCC,#0099FF,#0099FF,#008ae6,#545453,#0099FF,#5E5E5D,#0099FF,#CCCCCC,#FFFFFF,#CCCCCC,#FFFFFF,#545453,#333333,#FFFFFF,#CCCCCC,#CCCCCC,#333333,0099FF,#CCCCCC,#CCCCCC,#333333,#FFFFFF,#0099FF,#0099FF,#FFFFFF,#0099FF,,#FFFFFF,#CCCCCC,#7A7A7A,#CCCCCC,#5E5E5D,#000000,#CFCED3,#D3D3D3,#CC3522,#000000,#000000,#FFFFFF');
		$numElements = count($elements);

		for ($i=0; $i<$numElements; $i++) {
			if (!property_exists($templateData, $elements[$i])) {
				$templateData->{$elements[$i]} = $colors[$i];
			}
		}
	}


	public function compileCss(&$templateData, $cssFileName)
	{
		glz_importLib('lessphp/lessc.inc.php');
		$less = new lessc;
		$less->setImportDir(array(__DIR__.'/less/'));
		$css = file_get_contents(__DIR__.'/less/styles.less');
		$css = $this->applyCssVariables($templateData, $less, $css);
		$css = $this->applyFont($templateData, $less, $css);
		$css = $less->compile($css);
		$css = $this->fixUrl($css);
		$css = $this->addLogoAndCustomCss($templateData, $css);
		file_put_contents($cssFileName, $css);
		$templateData->css = '<link rel="stylesheet" href="'.$cssFileName.'" type="text/css" media="screen" />';
	}

	private function applyFont(&$templateData, $less, $css)
	{
		$font1 = $templateData->font1 == 'default' ? 'Titillium Web' : $templateData->font1;
		$font2 = $templateData->font2 == 'default' ? 'PT Sans' : $templateData->font2;

		$fonts = '@import url(http://fonts.googleapis.com/css?family='.str_replace(' ', '+', $font1).':400,700,600);'.PHP_EOL.
				 '@font-1: \''.$font1.'\', sans-serif;'.PHP_EOL.
				 '@import url(http://fonts.googleapis.com/css?family='.str_replace(' ', '+', $font2).':400,700);'.PHP_EOL.
			     '@font-2: \''.$font2.'\', sans-serif;'.PHP_EOL;
		return $css.PHP_EOL.$fonts;
	}

	private function fixUrl($css)
	{
		$css = str_replace(
				array('../img/', '../font/'),
				array('../static/movio/templates/'.self::TEMPLATE_NAME.'/img/', '../static/movio/templates/'.self::TEMPLATE_NAME.'/font/'),
				$css);
		return $css;
	}

	private function addLogoAndCustomCss(&$templateData, $css)
	{
		$templateData->headerLogo = @json_decode($templateData->headerLogo);
		if ($templateData->headerLogo) {
			$image = org_glizycms_mediaArchive_MediaManager::getMediaById($templateData->headerLogo->id);
			$fileName = $image->getFileName();
			
			$templateData->customCss .= <<<EOD
header .site-logo {
	background-image: url("../{$fileName}");
	background-repeat: no-repeat;
}
EOD;
		}

		$css .= PHP_EOL.$templateData->customCss;
		return $css;
	}

	private function applyCssVariables(&$templateData, $less, $css)
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
@text: @c-text;
@text-heading: @c-text-heading;
@color-link: @c-color-link;
@color-link-hover: @c-color-link-hover;
@breadkcrumbs-link: @c-color-link;
@breadkcrumbs-link-hover: @c-color-link-hover;

@navigation-background: @c-navigation-background;
@sidebar-link: @c-sidebar-link;
@sidebar-link-hover: @c-sidebar-link-hover;
@languages-link: @c-languages-link;
@languages-link-hover: @c-languages-link-hover;
@metanavigation-link: @c-metanavigation-link;
@metanavigation-link-hover: @c-metanavigation-link-hover;

@slider-background: @c-slider-background;
@slider-text: @c-slider-text;
@slider-bullet-background: @c-slider-text;
@slider-bullet-background-selected: darken(@c-slider-background, 5);;

@icon-in-page:lighten(@text, 30);
@icon-in-box:@c-icon-in-box;
@icon-in-box-background:@c-icon-in-box-background;

@box-image-border: @c-box-image-border;
@box-background: @c-box-background;
@box-border:@c-box-border;
@box-header-link:@c-box-header-link;
@box-relation-item-border:@c-box-image-border;
@box-text: @c-box-text;

@color-border-button: @c-color-border-button;
@color-arrow-button-slider: @c-color-arrow-button-slider;
@color-arrow-button-slider-hover: @c-color-arrow-button-slider-hover;

@form-border: @c-form-border;
@form-required: @c-form-required;
@form-button: @c-form-button;
@form-button-text: @c-form-button-text;
@form-button-primary: @c-form-button-primary;
@form-button-primary-text:@c-form-button-text;
@form-input-text:@c-form-input-text;
@form-input-background:@c-form-input-background;

@timeline-theme: @c-timeline-theme;

@storyteller-background: @c-storyteller-background;
@storyteller-icon: lighten(@c-text, 70);
@storyteller-border: @c-storyteller-border;
@storyteller-item-background: @c-storyteller-item-background;
@storyteller-comments-background: @c-storyteller-item-background;
@storyteller-link: @c-color-link;
@storyteller-link-hover: @color-link-hover;
@storyteller-navigation-link: @c-storyteller-navigation-link;
@storyteller-image-border: @c-box-image-border;

@footer-border: @c-footer-border;
@footer-text: @c-footer-text;

@svg-path-stroke:@c-svg-path-stroke;
@svg-main-node-background:@c-svg-main-node-background;
@svg-node-border:@c-svg-node-border;
@svg-text-link:@c-svg-text-link;
@svg-text-node:@c-svg-text-node;
@svg-text-main-node:@c-svg-text-main-node;
@svg-node-background:@c-svg-node-background;

EOD;
		return $css;
	}



	public static function homeSliderImage($item) {
	    return 'background:url('.$item->image['src'].') no-repeat 0 0;';
	}
}
