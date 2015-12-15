<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_components_SkinDefine extends org_glizy_components_Component
{
	var $_templateString;
	var $_skinType;

	/**
	 * Init
	 *
	 * @return	void
	 * @access	public
	 */
	function init()
	{
		$this->defineAttribute('skinType',				false, NULL, 	COMPONENT_TYPE_STRING);
		parent::init();
	}



	function process()
	{
		$this->_skinType = $this->getAttribute('skinType');
		if (is_null($this->_skinType))
		{
			$root = &$this->getRootComponent();
			$this->_skinType = $root->getAttribute('skinType');
			$this->setAttribute('skinType', $this->_skinType);
		}
		$this->_templateString 	= $this->getText();
	}

	function getTemplateString()
	{
		if ($this->getAttribute('skinType')=='PHPTAL')
		{
			$this->_templateString = str_replace('&gt;![CDATA[', '<![CDATA[', $this->_templateString);
			$this->_templateString = str_replace(']]&lt;', ']]>', $this->_templateString);
			return '<span tal:omit-tag="">'.$this->_templateString.'</span>';
		}
		else
		{
			return $this->_templateString;
		}
	}
}