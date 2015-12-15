<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_components_SearchBox extends org_glizy_components_Component
{
	/**
	 * Init
	 *
	 * @return	void
	 * @access	public
	 */
	function init()
	{
		$this->defineAttribute('label',		false, 	__T('GLZ_SEARCH_LABEL'),		COMPONENT_TYPE_STRING);
		$this->defineAttribute('buttonLabel',		false, 	__T('GLZ_SEARCH_BUTTON'),		COMPONENT_TYPE_STRING);
		$this->defineAttribute('cssClass',		false, 	'',		COMPONENT_TYPE_STRING);
		$this->defineAttribute('searchPageId',		false, 	'',		COMPONENT_TYPE_STRING);
		$this->defineAttribute('title',				false, 	__T('GLZ_SEARCH_BUTTON'),		COMPONENT_TYPE_STRING);
		parent::init();
	}

	function process()
	{
		parent::process();
		$this->_content['id'] 		= $this->getId();
		$this->_content['cssClass'] = $this->getAttribute('cssClass');
		$this->_content['title'] 	= $this->getAttributeString('title');
		$this->_content['label'] 	= $this->getAttributeString('label');
		$this->_content['buttonLabel'] 	= $this->getAttributeString('buttonLabel');
		$this->_content['value'] 	= __Request::get('search', '');
		if (!$this->_content['buttonLabel']) {
			$this->_content['buttonLabel'] = $this->_content['label'];
		}

		$searchPageId = $this->getAttribute('searchPageId');

		if (!is_int($searchPageId)) {
			$siteMap = $this->_application->getSiteMap();
			$menu = $siteMap->getMenuByPageType($searchPageId);
			if ($menu && $menu->isVisible) {
				$this->_content['__url__'] = org_glizy_helpers_Link::makeURL('link', array('pageId' => $menu->id));
			} else {
				$this->setAttribute('visible', false);
			}
		} else {
			$this->_content['__url__'] = org_glizy_helpers_Link::makeURL('link', array('pageId' => $searchPageId));
		}
	}
}

if (!class_exists('org_glizy_components_SearchBox_render'))
{
	class org_glizy_components_SearchBox_render extends org_glizy_components_render_Render
	{
		function getDefaultSkin()
		{
			$skin = <<<EOD
<div class="" tal:attributes="class SearchBox/cssClass">
	<h3 tal:content="SearchBox/title" />
	<form id="" method="post" action="" tal:attributes="id SearchBox/id; action SearchBox/__url__">
		<label for="search" tal:content="SearchBox/label" />
		<input type="text" name="search" id="search" class="text" tal:attributes="value SearchBox/value"/>
		<br />
		<table>
			<tr>
				<td class="span"></td>
				<td><input type="submit" class="submitButton" tal:attributes="value SearchBox/buttonLabel" /></td>
			</tr>
		</table>
	</form>
</div>
EOD;
			return $skin;
		}
	}
}