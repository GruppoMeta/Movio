<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_components_Breadcrumbs extends org_glizy_components_Component
{
	private $extraItem = null;

	/**
	 * Init
	 *
	 * @return	void
	 * @access	public
	 */
	function init()
	{
		$this->defineAttribute('label',	false, 	'',	COMPONENT_TYPE_STRING);
		$this->defineAttribute('separator',	false, 	' &gt; ',	COMPONENT_TYPE_STRING);
		$this->defineAttribute('cssClass',	false, 	'',	COMPONENT_TYPE_STRING);
		$this->defineAttribute('cssCurrent',	false, 	'current', 	COMPONENT_TYPE_STRING);
		$this->defineAttribute('menuId',		false, 	NULL,	COMPONENT_TYPE_STRING);

		// call the superclass for validate the attributes
		parent::init();

		$this->addEventListener(GLZ_EVT_BREADCRUMBS_UPDATE, $this);
		$this->addEventListener(GLZ_EVT_SITEMAP_UPDATE, $this);
	}

	function process()
	{
		$this->_content = new org_glizy_components_BreadcrumbsVO();
		$this->_content->label = $this->getAttribute('label');
		$this->_content->separator = $this->getAttribute('separator');
		$this->_content->cssClass = $this->getAttribute('cssClass');
		$menuId 		= $this->getAttribute('menuId');
		$siteMap 		= &$this->_application->getSiteMap();
		$currentMenu 	= is_null($menuId) ? $this->_application->getCurrentMenu() : $siteMap->getNodeById($menuId);

		if ($this->extraItem) {
			array_unshift($this->_content->records, '<span class="'.$this->getAttribute('cssCurrent').'">'.$this->extraItem.'</span>');
		}

		while (true)
		{
			if ($currentMenu->type!='SYSTEM')
			{
				$nodeTitle = empty($currentMenu->titleLink) ? $currentMenu->title : $currentMenu->titleLink;
				$nodeDescription = empty($currentMenu->linkDescription) ? $nodeTitle: $currentMenu->linkDescription;
				if (count($this->_content->records))
				{
					if (empty($currentMenu->url))
					{
						array_unshift($this->_content->records, org_glizy_helpers_Link::makeLink('link', array('pageId' => $currentMenu->id, 'title' => $nodeDescription, 'label' => $nodeTitle)));
					}
					else
					{
						array_unshift($this->_content->records, org_glizy_helpers_Link::makeSimpleLink($nodeTitle, $currentMenu->url, $nodeDescription));
					}
				}
				else
				{
					array_unshift($this->_content->records, '<span class="'.$this->getAttribute('cssCurrent').'">'.$nodeTitle.'</span>');
				}
			}
			if ($currentMenu->parentId===0 || !$currentMenu->parentId) break;
			$tempNode = &$currentMenu->parentNode();
			$currentMenu = &$tempNode;
		}
	}


	function siteMapUpdate($event)
	{
		if (!is_null($event->data)) {
			$this->setAttribute('menuId', $event->data);
		}
		$this->process();
	}

	function onBreadcrumbsUpdate($event)
	{
		$this->extraItem = $event->data;
		$this->process();
	}
}

class org_glizy_components_BreadcrumbsVO
{
	var $separator = '';
	var $label = '';
	var $cssClass = '';
	var $records = array();
}


class org_glizy_components_Breadcrumbs_render extends org_glizy_components_render_Render
{
	function getDefaultSkin()
	{
		$skin = <<<EOD
<ul tal:condition="php: count(Component.records)">
	<li tal:condition="Component/label" tal:content="Component/label" />
	<li tal:repeat="item Component/records"><span tal:omit-tag="" tal:content="structure item" /><span tal:condition="not: repeat/item/end" tal:content="structure Component/separator" /></li>
</ul>
EOD;
		return $skin;
	}
}