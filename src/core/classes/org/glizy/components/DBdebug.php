<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_components_DBdebug extends org_glizy_components_Component
{

	function init()
	{
		// define the custom attributes
		$this->defineAttribute('value', false, true, COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('connect', false, 0, COMPONENT_TYPE_INTEGER);

		// call the superclass for validate the attributes
		parent::init();
	}

	function process()
	{
		glz_import('org.glizy.dataAccess.DataAccess');
		glz_DBdebug( $this->getAttribute('value'), $this->getAttribute('connect') );
	}

}