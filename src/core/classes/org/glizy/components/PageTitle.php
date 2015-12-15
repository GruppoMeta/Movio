<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_components_PageTitle extends org_glizy_components_Component
{
	private $currentMenu;

	/**
	 * Init
	 *
	 * @return	void
	 * @access	public
	 */
	function init()
	{
		$this->defineAttribute('tag',		false, 	'h1',	COMPONENT_TYPE_STRING);
		$this->defineAttribute('cssClass',	false, 	'',		COMPONENT_TYPE_STRING);
		$this->defineAttribute('wrapCssClass',	false, 	'',		COMPONENT_TYPE_STRING);
		$this->defineAttribute('wrap',	false, 	false,		COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('drawIcon',	false, 	false,		COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('value',		false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('menuDepth',		false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('menuId',		false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('setSiteMapTitle',	false, 	false,	COMPONENT_TYPE_BOOLEAN );

		// call the superclass for validate the attributes
		parent::init();

		$this->addEventListener(GLZ_EVT_SITEMAP_UPDATE, $this);
		$this->addEventListener(GLZ_EVT_PAGETITLE_UPDATE, $this);
	}


	function process()
	{
		if (is_null($this->getAttribute('value')))
		{
			$menuId 		= $this->getAttribute('menuId');
			$siteMap 		= &$this->_application->getSiteMap();
			$this->currentMenu 	= is_null($menuId) ? $this->_application->getCurrentMenu() : $siteMap->getNodeById($menuId);

			if (is_null($this->getAttribute('menuDepth')))
			{
				$this->_content = $this->currentMenu->title;
			}
			else
			{
				$this->currentMenu = &$this->currentMenu->parentNodeByDepth( $this->getAttribute('menuDepth') );
				$this->_content = $this->currentMenu->title;
			}
		}
		else
		{
			$this->_content = glz_encodeOutput($this->getAttribute('value'));
		}

		if ( $this->getAttribute( 'setSiteMapTitle' ) )
		{
			$menu = $this->_application->getCurrentMenu();
			$menu->title = html_entity_decode( $this->_content );
		}
	}


	function render_html()
	{
		if (!empty($this->_content))
		{
			$tag = $this->getAttribute('tag');
			$cssClass = $this->getAttribute('cssClass');
			$output  = '<'.$tag.($cssClass ? ' class="'.$cssClass.'"' : '').'>';
			if ($this->getAttribute('drawIcon') && $this->currentMenu->icon) {
				$output .= '<i class="'.$this->currentMenu->icon.'"></i> ';
			}
			$output .= $this->_content.'</'.$tag.'>';

			if ($this->getAttribute('wrap')) {
				$wrapCssClass = $this->getAttribute('wrapCssClass');
				$output  = '<div'.($wrapCssClass ? ' class="'.$wrapCssClass.'"' : '').'>'.$output.'</div>';
			}

			$this->addOutputCode($output);
		}
	}

	function siteMapUpdate($event)
	{
		if (!is_null($event->data)) {
			$this->setAttribute('menuId', $event->data);
		}
		$this->process();
	}

	function onPageTitleUpdate($event)
	{
		if (!is_null($event->data)) {
			$this->_content = $event->data;
		}
	}
}