<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_components_Module extends org_glizy_components_ComponentContainer
{

	function __construct(&$application, &$parent, $tagName='', $id='', $originalId='')
	{
		parent::__construct($application, $parent, $tagName, $id, $originalId);
	}

	/**
	 * Init
	 *
	 * @return	void
	 * @access	public
	 */
	function init()
	{
		$this->canHaveChilds = true;
		$this->overrideEditableRegion = false;

		// define the custom attributes
		$this->defineAttribute('adm:editComponents',	false, array(), 	COMPONENT_TYPE_ENUM);

		// call the superclass for validate the attributes
		parent::init();
	}
}