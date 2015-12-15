<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizy_components_LinkTo extends org_glizy_components_Component
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
		$this->defineAttribute('label',				false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('required',			false, 	false,	COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('requiredMessage',	false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('wrapTag',	false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('cssClass',	false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('title',	false, 	NULL,	COMPONENT_TYPE_STRING);

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
		$this->_content = $this->_parent->loadContent($this->getId());

		if ($this->_parent->_tagname=='glz:Page')
		{
			header('location: '.$this->_content );
			exit();
		}
	}

	function render_html()
	{
		$this->addOutputCode( $this->_render() );
	}

	function getContent()
	{
		return $this->_render();
	}

	function _render()
	{
		if ( !empty(  $this->_content ) )
		{
			$cssClass = $this->getAttribute('cssClass');
			$wrapStart = '';
			$wrapEnd = '';
			if (!is_null($this->getAttribute('wrapTag')))
			{
				if (!is_null( $cssClass ) )
				{
					$wrapperCssClass = ' class="'.$cssClass.'"';
				}
				$wrapStart = '<'.$this->getAttribute('wrapTag').$wrapperCssClass.'>';
				$wrapEnd = '</'.$this->getAttribute('wrapTag').'>';
			}

			$url = $this->_content;
			$label = $url;
			if (intval($url)) {
				// link interno
				$siteMap = $this->_application->getSiteMap();
				$menu = $siteMap->getNodeById($url);
				$label = $menu->title;
				$url = org_glizy_helpers_Link::makeURL('link', array('pageId' => $url));
			}

			$output = org_glizy_helpers_Link::formatLink( $url, $label, NULL, $cssClass );
			return $wrapStart.$output.$wrapEnd;
		}

		return '';
	}

	public static function translateForMode_edit($node) {
		$attributes = array();
		$attributes['id'] = $node->getAttribute('id');
		$attributes['label'] = $node->getAttribute('label');

		if (count($node->attributes))
		{
			foreach ( $node->attributes as $index=>$attr )
			{
				if ($attr->prefix=="adm")
				{
					$attributes[$attr->name] = $attr->value;
				}
			}
		}

		return org_glizy_helpers_Html::renderTag('glz:Input', $attributes);
	}
}
