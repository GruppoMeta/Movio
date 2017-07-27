<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

/**
 * Trait org_glizy_mvc_core_AuthenticatedCommandTrait
 */
trait org_glizy_mvc_core_AuthenticatedCommandTrait
{
	/** @var org_glizy_application_User $user */
	protected $user = NULL;

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
        	$canAccess = $this->user->acl($service, $action);
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