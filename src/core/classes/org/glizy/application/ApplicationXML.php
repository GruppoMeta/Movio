<?php
/**
 * Application  class.
 *
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizy_application_ApplicationXML extends org_glizy_application_Application
{
	function _initSiteMap($forceReload=false)
	{
		$this->siteMap = &org_glizy_ObjectFactory::createObject('org.glizy.application.SiteMapXML');
		$this->siteMap->getSiteArray($forceReload);
	}
}