<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizycms_views_components_Metadata extends org_glizy_components_Component
{
	function render_html()
	{
		$menu = $this->_application->getCurrentmenu();
		$language = $this->_application->getLanguage();
		$description = org_glizy_ObjectValues::get('org.glizy.og', 'description', $menu->description );
        $keywords = org_glizy_ObjectValues::get('org.glizy.og', 'keywords', $menu->keywords );

		$this->addOutputCode(
			$this->renderMetadata(glz_htmlentities($description), glz_htmlentities($keywords), $language)
		);

		if (__Config::get('glizycms.dublincore.enabled')) {
           $menuProxy = org_glizy_ObjectFactory::createObject('org.glizycms.contents.models.proxy.MenuProxy');
           $arMenu = $menuProxy->getMenuFromId($menu->id, $this->_application->getLanguageId());

			$this->addOutputCode(
				$this->renderDublinCore($arMenu, $language)
			);
		}
	}

	/**
	 * @param  string $description
	 * @param  strin $keywords
	 * @param  string $language
	 * @return string
	 */
	private function renderMetadata($description, $keywords, $language)
	{
		$metadata = <<<EOD
<meta http-equiv="content-language" content="{$language}" />
<meta name="keywords" content="{$keywords}" />
<meta name="description" content="{$description}" />
EOD;
		return $metadata;
	}

	/**
	 * @param  org_glizy_dataAccessDoctrine_AbstractActiveRecord $menu
	 * @param  string $language
	 * @return string
	 */
	private function renderDublinCore($menu, $language)
	{
		$metadata = <<<EOD
<link rel="schema.DC" href="http://purl.org/dc/elements/1.1/" />
<meta name="DC.Title" content="{$menu->menudetail_title}" />
<meta name="DC.Creator" content="{$menu->menudetail_creator}" />
<meta name="DC.Subject" content="{$menu->menudetail_subject}" />
<meta name="DC.Description" content="{$menu->menudetail_description}" />
<meta name="DC.Publisher" content="{$menu->menudetail_publisher}" />
<meta name="DC.Contributor" content="{$menu->menudetail_contributor}" />
<meta name="DC.Date" content="(SCHEME=ISO8601) {$menu->menu_modificationDate}" />
<meta name="DC.Type" content="{$menu->menudetail_type}" />
<meta name="DC.Format" content="(SCHEME=IMT) text/html" />
<meta name="DC.Identifier" content="{$menu->menudetail_identifier}" />
<meta name="DC.Source" content="{$menu->menudetail_source}" />
<meta name="DC.Language" content="(SCHEME=ISO639-1) {$language}" />
<meta name="DC.Relation" content="{$menu->menudetail_relation}" />
<meta name="DC.Coverage" content="{$menu->menudetail_coverage}" />
<meta name="DC.Rights" content="" />
EOD;
		return $metadata;
	}
}
