<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2011 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizycms_modulesManager_controllers_ajax_Enable extends org_glizy_mvc_core_CommandAjax
{
	function execute()
	{
		$id = __Request::get( 'id' );
		$modulesState = org_glizy_Modules::getModulesState();
		$modulesState[ $id ] = __Request::get( 'action' ) == 'enable';
		org_glizy_Modules::setModulesState( $modulesState );
		return true;
	}
}