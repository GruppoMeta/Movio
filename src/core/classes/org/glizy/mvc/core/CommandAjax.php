<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

/** class  org_glizy_mvc_core_CommandAjax */
class org_glizy_mvc_core_CommandAjax extends GlizyObject
{
	protected $controller = NULL;
	protected $view = NULL;
    /** @var org_glizy_mvc_core_Application $application */
	protected $application = NULL;
    /** @var org_glizy_application_User $user */
	protected $user = NULL;
	public $directOutput = false;

	function __construct( $view=NULL, $application=NULL )
	{
		$this->controller = $view;
		$this->view = $view;
		$this->application = is_null( $application ) && !is_null( $view ) ? $view->_application : $application;
		$this->user = &$this->application->getCurrentUser();
	}

	function execute( $oldAction=null )
	{
		return true;
	}

	function changePage( $routingName, $option=array() )
	{
		$url = __Link::makeUrl( $routingName, $option );
		$url = str_replace( "ajax", "index", $url );
		return $url;
	}

	function changeAction( $action )
	{
		$url = __Link::makeUrl( 'linkChangeAction', array( 'action' => $action ) );
		$url = str_replace( "ajax", "index", $url );
		return $url;
	}

}