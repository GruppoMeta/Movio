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
    /** @var org_glizy_components_Component $controller */
	protected $controller = NULL;
	/** @var org_glizy_components_Component $view */
	protected $view = NULL;
    /** @var org_glizy_mvc_core_Application $application */
	protected $application = NULL;
    /** @var org_glizy_application_User $user */
	protected $user = NULL;
	public $directOutput = false;

	/**
	 * @param org_glizy_mvc_components_Component $view
	 * @param org_glizy_mvc_core_Application $application
	 */
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


	// NOTA: codice duplicato da org_glizy_mvc_core_AuthenticatedCommandTrait
	// necessario per compatibilità con php 5.3

	/**
     * Check if the user is logged
     */
    protected function checkIsLogged($service=null, $action=null)
    {
        if (!$this->user->isLogged()) {
            org_glizy_helpers_Navigation::accessDenied();
        }
    }


	/**
	 * Check the user permission
	 * @param  string $service
	 * @param  string $action [description]
	 */
	protected function checkPermission($service=null, $action=null)
	{
		$canAccess = $this->user->isLogged();

        if ($canAccess && $service && $action) {
        	$canAccess = $this->user->acl($service, $action, false);
        }

        if (!$canAccess) {
            org_glizy_helpers_Navigation::accessDenied();
        }
	}

    /**
     * Check the user permission
     * @param  string $service
     * @param  string $action [description]
     */
    protected function checkPermissionForBackend($service=null, $action=null)
    {
        if (!$this->user->backEndAccess) {
            org_glizy_helpers_Navigation::accessDenied();
        }

        $this->checkPermission($service, $action);
    }
}