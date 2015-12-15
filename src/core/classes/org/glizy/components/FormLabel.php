<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_components_FormLabel extends org_glizy_components_HtmlComponent
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
		$this->defineAttribute('for',	false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('text',	true, 	NULL,	COMPONENT_TYPE_STRING);

		$this->setHtmlTag('label');
		// call the superclass for validate the attributes
		parent::init();
	}
}