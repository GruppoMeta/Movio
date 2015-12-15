<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_components_Logout extends org_glizy_components_Component
{
	var $_error = NULL;

	/**
	 * Init
	 *
	 * @return	void
	 * @access	public
	 */
	function process()
	{
		$authClass = org_glizy_ObjectFactory::createObject(__Config::get('glizy.authentication'));
		if ($authClass) {
			$authClass->logout();
		}

		org_glizy_helpers_Navigation::gotoUrl( GLZ_HOST );
		exit();
	}
}