<?php
class Template extends GlizyObject
{
	function render($application, $view, $templateData)
	{
		$templateProxy = org_glizy_ObjectFactory::createObject('org.glizycms.template.models.proxy.TemplateProxy');
		// $templateProxy->invalidateCache();
        $cache = $templateProxy->getTemplateCache();

        $cssFileName = __Paths::get('CACHE').md5(__DIR__.'_'.$templateData->__id.__Config::get('APP_VERSION')).'.css';
        $self = $this;
		$templateData = $cache->get($cssFileName, array(), function() use ($self, $templateData, $view, $cssFileName) {
			$self->updateTemplateData($templateData);
			$self->compileCss($templateData, $cssFileName);
			return $templateData;
		});

		$view->addOutputCode($templateData->css, 'css');
		$this->renderTemplateHeader($view, $templateData);

		if (!in_array($view->getAttribute('templateFileName'), array('1col.php', '2cols.php', '3cols.php', 'cover.php'))) {
			$view->setAttribute('templateFileName', '2cols.php');
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

		$css = str_replace(	array('../img/', '../font/'),
				array('../static/movio/templates/Movio/img/', '../static/movio/templates/Movio/font/'),
				$css);
		$css .= PHP_EOL.$templateData->customCss;
		file_put_contents($cssFileName, $css);
		$templateData->css = '<link rel="stylesheet" href="'.$cssFileName.'" type="text/css" media="screen" />';
	}

	public function updateTemplateData(&$templateData)
	{
		$elements = explode(',', 'c-body-background,c-text,c-text-heading,c-color-link,c-metanavigation-background,c-metanavigation-link,c-metanavigation-background-hover,c-slider-background,c-slider-text,c-sidebar-background,c-sidebar-background-hover,c-sidebar-link,c-sidebar-link-hover,c-box-border,c-box-background,c-box-header-background,c-box-header-link,c-box-text,c-box-image-border,c-icon-in-box,c-icon-in-box-background,c-color-border-button,c-color-background-button,c-color-arrow-button-slider,c-color-arrow-button-slider-hover,c-timeline-theme,c-footer-background,c-footer-border,c-footer-text,c-storyteller-background,c-storyteller-border,c-storyteller-item-background,c-storyteller-navigation-link,c-form-border,c-form-required,c-form-button-primary,c-form-button-gradient-1,c-form-button-primary-text,c-form-input-text,c-form-input-background,c-svg-path-stroke,c-svg-node-border,c-svg-main-node-background,c-svg-text-link,c-svg-text-node,c-svg-text-main-node,c-svg-node-background');
		$colors = explode(',', '#FFFFFF,#000000,#2F2F2F,#CC3522,#CC3522,#FFFFFF,#B82013,#CC3522,#FFFFFF,#F5F5F5,#CC3522,#000000,#FFFFFF,#C6C6C6,#FAFAFA,#F3F3F3,#000000,#000000,#C6C6C6,#FFFFFF,#A1A1A1,#D6D6D6,#FFFFFF,#A1A1A1,#CB3521,#CC3522,#363636,#5E5E5E,#FFFFFF,#E4E4E4,#D8D8D8,#F9F9F9,#7A7A7A,#E9E9E9,#E9E9E9,#B82013,#CACACA,#FFFFFF,#5D5D5D,#F9F9F9,#000000,#CFCED3,#D3D3D3,#CC3522,#000000,#000000,#FFFFFF');
		$numElements = count($elements);

		for ($i=0; $i<$numElements; $i++) {
			if (!property_exists($templateData, $elements[$i])) {
				$templateData->{$elements[$i]} = $colors[$i];
			}
		}
	}

	private function applyCssVariables(&$templateData, $less, $css)
	{
		$variables = array();
		foreach ($templateData as $k=>$v) {
			if (strpos($k, 'c-')===0) {
				if (strlen($v)!=7) {
					$v = '#000000';
				}
				$variables[$k] = $v;
			}
		}
		$less->setVariables($variables);

		$css .= <<<EOD
@text: @c-text;
@text-heading: @c-text-heading;
@body-background: @c-body-background;
@color-link: @c-color-link;
@color-link-hover: darken(@c-color-link, 5);
@breadkcrumbs-link: @color-link-hover;
@breadkcrumbs-link-hover: @color-link-hover;

@metanavigation-background: @c-metanavigation-background;
@metanavigation-background-hover: @c-metanavigation-background-hover;
@metanavigation-link: @c-metanavigation-link;
@metanavigation-link-hover: @c-metanavigation-link;

@slider-background: @c-slider-background;
@slider-text: @c-slider-text;
@slider-read-more-background: darken(@c-slider-background, 5);;
@slider-bullet-background: @c-slider-text;
@slider-bullet-background-selected: darken(@c-slider-background, 5);;

@sidebar-background: @c-sidebar-background;
@sidebar-background-hover: @c-sidebar-background-hover;
@sidebar-border: @c-box-border;
@sidebar-link: @c-sidebar-link;
@sidebar-link-hover: @c-sidebar-link-hover;
@sidebar-arrow: darken(@c-sidebar-link-hover, 37);
@sidebar-arrow-hover: @c-sidebar-link-hover;

@sidebar-sub-background: lighten(@c-sidebar-background, 2);
@sidebar-sub-background-hover:  @c-sidebar-background-hover;
@sidebar-sub-border: @c-box-border;
@sidebar-sub-link: @c-sidebar-link;
@sidebar-sub-link-hover: @c-sidebar-link-hover;
@sidebar-box-background: lighten(@c-sidebar-background, 2);

@inner-nav-link: @text-heading;
@inner-nav-link-hover: @color-link;

@box-border: @c-box-border;
@box-background: @c-box-background;
@box-header-background: @c-box-header-background;
@box-header-link: @c-box-header-link;
@box-image-border: @c-box-image-border;
@box-text: @c-box-text;
@box-relation-item-border: lighten(@c-box-image-border, 6);
@box-relation-item-background: @body-background;
@box-relation-item-link: @c-text;
@box-relation-item-link-hover: @c-color-link;

@icon-in-page: lighten(@c-text, 70);
@icon-in-box: @c-icon-in-box;
@icon-in-box-background: @c-icon-in-box-background;

@color-border-button: @c-color-border-button;
@color-background-button: @c-color-background-button;
@color-arrow-button-slider: @c-color-arrow-button-slider;
@color-arrow-button-slider-hover: @c-color-arrow-button-slider-hover;

@timeline-theme: @c-timeline-theme;

@footer-background: @c-footer-background;
@footer-border: @c-footer-border;
@footer-text: @c-footer-text;
@footer-text2: darken(@footer-text, 28);

@storyteller-background: @c-storyteller-background;
@storyteller-icon: lighten(@c-text, 70);
@storyteller-grandient-1: lighten(@c-storyteller-background, 3);
@storyteller-grandient-2: lighten(@c-storyteller-background, 10);
@storyteller-border: @c-storyteller-border;
@storyteller-item-background: @c-storyteller-item-background;
@storyteller-comments-background: @c-storyteller-item-background;
@storyteller-link: @c-color-link;
@storyteller-navigation-link: @c-storyteller-navigation-link;
@storyteller-image-border: @c-box-image-border;
@storyteller-shadow: darken(@storyteller-background, 25);

@form-border: @c-form-border;
@form-required: @c-form-required;
@form-button-primary: @c-form-button-primary;
@form-buttonPrimary-gradient-2:darken(@c-form-button-primary, 4);
@form-buttonPrimary-gradient-1:lighten(@c-form-button-primary, 24);
@form-button-gradient-1:@c-form-button-gradient-1;
@form-button-gradient-2:darken(@c-form-button-gradient-1, 15);;
@form-buttonPrimary-text:@c-form-button-primary-text;
@form-input-text:@c-form-input-text;
@form-input-background:@c-form-input-background;

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


	private function applyFont(&$templateData, $less, $css)
	{
		$font1 = $templateData->font1 == 'default' ? 'Titillium Web' : $templateData->font1;
		$font2 = $templateData->font2 == 'default' ? 'Open Sans' : $templateData->font2;

		$fonts = '@import url(https://fonts.googleapis.com/css?family='.str_replace(' ', '+', $font1).':300,600);'.PHP_EOL.
				 '@font-1: \''.$font1.'\', sans-serif;'.PHP_EOL.
				 '@import url(https://fonts.googleapis.com/css?family='.str_replace(' ', '+', $font2).':400,700,600);'.PHP_EOL.
			     '@font-2: \''.$font2.'\', sans-serif;'.PHP_EOL;
		return $css.PHP_EOL.$fonts;
	}

	private function renderTemplateHeader($view, $templateData)
	{
	    $siteProp = unserialize(org_glizy_Registry::get(__Config::get('REGISTRY_SITE_PROP').$view->_application->getLanguage(), ''));
        $view->addOutputCode($siteProp['title'], 'title1');
		$view->addOutputCode($siteProp['subtitle'], 'title2');

	    /*
		$view->addOutputCode(str_replace('../getImage.php', 'getImage.php', $templateData->title1), 'title1');
		$view->addOutputCode(str_replace('../getImage.php', 'getImage.php', $templateData->title2), 'title2');
		*/
		$view->addOutputCode(str_replace('../getImage.php', 'getImage.php', $templateData->title3), 'title3');
		$view->addOutputCode(str_replace('../getImage.php', 'getImage.php', $templateData->title4), 'title4');
		$templateData->footerLogo = @json_decode($templateData->footerLogo);
		if ($templateData->footerLogo && $templateData->footerLogo->id) {
			$image = org_glizy_helpers_Media::getImageById($templateData->footerLogo->id);
			if ($templateData->footerLogoLink) {
				$image = __Link::formatLink($templateData->footerLogoLink, $templateData->footerLogoTitle, $image);
			}
			$view->addOutputCode($image, 'logoFooter');
		}

		$view->addOutputCode(org_glizy_helpers_CSS::CSScode($templateData->customCss), 'head');
	}

	public static function homeSliderImage($item) {
	    return 'background:url('.$item->image['src'].') no-repeat 270px 0;';
	}
}
