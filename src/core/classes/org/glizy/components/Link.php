<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_components_Link extends org_glizy_components_Component
{
	/**
	 * Init
	 *
	 * @return	void
	 * @access	public
	 */
	function init()
	{
		// define the custom attributes
		$this->defineAttribute('pageId',	false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('routeUrl',	false, 	'link',	COMPONENT_TYPE_STRING);
		$this->defineAttribute('label',		false, 	'',		COMPONENT_TYPE_STRING);
		$this->defineAttribute('wrapTag',	false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('cssClass',	false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('url',		false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('icon',		false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('target',	false, 	'',	COMPONENT_TYPE_STRING);

		// call the superclass for validate the attributes
		parent::init();
	}


	/**
	 * Process
	 *
	 * @return	boolean	false if the process is aborted
	 * @access	public
	 */
	function process()
	{
		$currentMenu 	= &$this->_application->getCurrentMenu();
		$this->_content = array();
		$this->_content['id'] 		= $this->getId();
		$this->_content['label'] 	= $this->getAttribute('label');
		$this->_content['title'] 	= $this->_content['label'];
		$this->_content['icon'] 	= $this->getAttribute('icon');
		$this->_content['pageId'] 	= !is_null($this->getAttribute('pageId')) ? $this->getAttribute('pageId')  : $currentMenu->id;
	}


	/**
	 * Render
	 *
	 * @return	void
	 * @access	public
	 */
	function render_html()
	{
		$url = $this->getAttribute('url');
		$cssClass = $this->getAttribute('cssClass');
		$wrapStart = '';
		$wrapEnd = '';
		if (!is_null($this->getAttribute('wrapTag')))
		{
			if (!is_null( $cssClass ) )
			{
				$wrapperCssClass = ' class="'.$cssClass.'"';
			} else {
                $wrapperCssClass = '';
            }
			$wrapStart = '<'.$this->getAttribute('wrapTag').$wrapperCssClass.'>';
			$wrapEnd = '</'.$this->getAttribute('wrapTag').'>';
		}

		if ( is_null( $url ) )
		{
			$output = org_glizy_helpers_Link::makeLink2($this->getAttribute('routeUrl'), $this->_content, array('class' => $cssClass, 'id' => $this->getId()));
		}
		else
		{
			$output = org_glizy_helpers_Link::makeSimpleLink( $this->_content['label'], $url, '', $cssClass, '', array('target' => $this->getAttribute('target'), 'icon' => $this->getAttribute('icon'), 'id' => $this->getId()) );
		}

		$this->addOutputCode( $wrapStart.$output.$wrapEnd);
	}
}