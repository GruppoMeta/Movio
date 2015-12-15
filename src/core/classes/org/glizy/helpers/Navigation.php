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
    static function gotoUrl($location, $params=null, $hash='')
	{
		if ($params) {
			$location .= (strpos($location, '?')===false ? '?' : '&').http_build_query($params);
		}
		header('Location: '.$location.$hash);
		echo '<html><head><meta http-equiv="refresh" content="1;url='.$location.'"/></head></html>';
		exit;
	}

	function goHere()
	{
        /** @var org_glizy_application_Application $application */
		$application = &org_glizy_ObjectValues::get('org.glizy', 'application');
		org_glizy_helpers_Navigation::gotoUrl( org_glizy_helpers_Link::makeUrl( 'link', array( 'pageId' => $application->getPageId() ) ) );
	}
}