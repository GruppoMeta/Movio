<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizy_mvc_core_Application extends org_glizy_application_Application
{
	protected $proxyMap = array();
	public $useXmlSiteMap = false;

    /**
     * @param bool $readPageId
     */
	function _startProcess($readPageId=true)
	{
		foreach( $this->proxyMap as $k=>$v )
		{
			$v->onRegister();
		}

		parent::_startProcess($readPageId);
	}

    /**
     * @param string $className
     *
     * @return GlizyObject|mixed
     */
	function registerProxy( $className )
	{
		if ( array_key_exists( $className, $this->proxyMap ) )
		{
			new org_glizy_Exception( '[mvc:Application] Proxy già registrato' );
		}

		$classObj = __ObjectFactory::createObject( $className, $this );

		if ( is_object( $classObj ) )
		{
			$this->proxyMap[ $className ] = $classObj;
			return $classObj;
		}
		else
		{
			new org_glizy_Exception( '[mvc:Application] Proxy non trovato '.$className );
		}
	}

    /**
     * @param string $className
     *
     * @return null|
     */
	function retrieveProxy( $className )
	{
		if ( array_key_exists( $className, $this->proxyMap ) )
		{
			return $this->proxyMap[ $className ];
		}

		return null;
	}

    /**
     * @param $className
     *
     * @return GlizyObject|mixed
     */
	function retrieveService( $className )
	{
		$classObj = __ObjectFactory::createObject( $className, $this );
		return $classObj;
	}

    /**
     * @param bool $forceReload
     */
	function _initSiteMap($forceReload=false)
	{
		if ( $this->useXmlSiteMap )
		{
			$this->siteMap = org_glizy_ObjectFactory::createObject('org.glizy.application.SiteMapXML');
			$this->siteMap->getSiteArray($forceReload);
		}
		else
		{
			parent::_initSiteMap();
		}
	}

    /**
     * @param string $command
     *
     * @return mixed
     */
	function executeCommand( $command )
	{
		$actionClass = &org_glizy_ObjectFactory::createObject( $command, null, $this );
		if ( is_object( $actionClass ) && method_exists( $actionClass, "execute" ) ) {
			$params = func_get_args();
			array_shift($params);
			return call_user_func_array( array( $actionClass, "execute" ), $params );
		}
        return null;
	}
}