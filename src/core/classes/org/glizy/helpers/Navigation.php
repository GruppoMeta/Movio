<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizy_helpers_Navigation extends GlizyObject
{

    /**
     * @param $location
     * @param $params
     */
    public static function gotoUrl($location, $params=null, $hash='')
	{
		if ($params) {
			$location .= (strpos($location, '?')===false ? '?' : '&').http_build_query($params);
		}
		header('Location: '.$location.$hash);
		echo '<html><head><meta http-equiv="refresh" content="1;url='.$location.'"/></head></html>';
		exit;
	}

	public static function goHere()
	{
        /** @var org_glizy_application_Application $application */
		$application = &org_glizy_ObjectValues::get('org.glizy', 'application');
		org_glizy_helpers_Navigation::gotoUrl( org_glizy_helpers_Link::makeUrl( 'link', array( 'pageId' => $application->getPageId() ) ) );
	}

    /**
     * Show Access Denied page
     */
    public static function accessDenied($userIsLogged=false)
    {
        org_glizy_Session::set('glizy.loginUrl', org_glizy_helpers_Link::scriptUrl());

        if (!$userIsLogged && org_glizy_Routing::exists('login')) {
            org_glizy_Session::set('glizy.loginError', __Tp('LOGGER_INSUFFICIENT_GROUP_LEVEL'));
            org_glizy_Session::set('glizy.loginUrl', org_glizy_helpers_Link::scriptUrl());
            self::gotoUrl(org_glizy_Routing::makeUrl('login'));
        } else if (org_glizy_Routing::exists('accessDenied')) {
            self::gotoUrl(org_glizy_Routing::makeUrl('accessDenied'));
        } else {
            org_glizy_Exception::show403(__T('Access is denied'));
        }
    }

    public static function notFound($message='')
    {
        $message = $message ? : __T('GLZ_ERR_404');
        org_glizy_Exception::show404($message);
    }

}
