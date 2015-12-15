<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_components_ConfigValue extends org_glizy_components_Component
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
		$this->defineAttribute('key',		true, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('value',		false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('action',	false, 	'get',	COMPONENT_TYPE_STRING);

		// call the superclass for validate the attributes
		$this->doLater($this, 'preProcess');
		parent::init();
	}

	function preProcess()
	{
		if ($this->getAttribute('action')=='set')
		{
			$value = is_null($this->getAttribute('value')) ? $this->getContent() : $this->getAttribute('value');
			org_glizy_Config::set( $this->getAttribute('key'), $value );
		}
	}

	/**
	 * Render
	 *
	 * @return	void
	 * @access	public
	 */
	function render_html()
	{
		if ($this->getAttribute('action')=='get')
		{
			$this->addOutputCode(org_glizy_Config::get($this->getAttribute('key')));
		}
	}
}