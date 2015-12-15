<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_components_Tab extends org_glizy_components_State
{
	var $_state;

	/**
	 * Init
	 *
	 * @return	void
	 * @access	public
	 */
	function init()
	{
		// define the custom attributes
		$this->defineAttribute('label', true, '', COMPONENT_TYPE_STRING);
		$this->defineAttribute('url', false, NULL, COMPONENT_TYPE_STRING);
		$this->defineAttribute('draw', false , true, COMPONENT_TYPE_BOOLEAN);
		// call the superclass for validate the attributes
		parent::init();
	}
}