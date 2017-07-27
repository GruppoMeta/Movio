<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

/**
 * Class org_glizy_Modules
 */
class org_glizy_Modules
{

    /**
     * @return null|org_glizy_ModuleVO
     */
	public static function &getModules()
	{
		$modules = &org_glizy_ObjectValues::get('org.glizy', 'modules', array());
		return $modules;
	}

    /**
     * @return null|org_glizy_ModuleVO
     */
	public static function &getModulesSorted()
	{
		$modules = &org_glizy_ObjectValues::get('org.glizy', 'modules', array());
		uasort($modules, function($a, $b) {
				return strnatcasecmp($a->name, $b->name);
			});
		return $modules;
	}

    /**
     * @param $id
     * @return org_glizy_ModuleVO|null
     */
    public static function getModule( $id )
	{
		$modules = &org_glizy_ObjectValues::get( 'org.glizy', 'modules', array() );
		return isset( $modules[ $id ] ) ? $modules[ $id ] : null;
	}

    /**
     * @param org_glizy_ModuleVO $moduleVO
     */
	function addModule( org_glizy_ModuleVO $moduleVO )
	{
		$modules = &org_glizy_ObjectValues::get( 'org.glizy', 'modules', array() );
		$modulesState = self::getModulesState();
		if ( isset( $modulesState[ $moduleVO->id ] ) && !$modulesState[ $moduleVO->id ] )
		{
			$moduleVO->enabled = false;
		}
		$modules[ $moduleVO->id ] = $moduleVO;


        if ($moduleVO->enabled && $moduleVO->path) {
            __Paths::addClassSearchPath($moduleVO->path);
        }
	}

    /**
     * @return org_glizy_ModuleVO
     */
	function getModuleVO()
	{
		return new org_glizy_ModuleVO();
	}


    /**
     * @return array|mixed
     */
    static function getModulesState()
	{
		$pref = unserialize( org_glizy_Registry::get( __Config::get( 'BASE_REGISTRY_PATH' ).'/modules', '') );
		if (empty($pref))
		{
			$pref = array();
		}
		return $pref;
	}

    /**
     * @param $state
     */
	function setModulesState( $state )
	{
		org_glizy_Registry::set( __Config::get( 'BASE_REGISTRY_PATH' ).'/modules', serialize( $state ) );
		org_glizy_cache_CacheFile::cleanPHP();
	}

	static function deleteCache()
	{
		org_glizy_cache_CacheFile::cleanPHP();
	}

	function dump()
	{
		$modules = &org_glizy_ObjectValues::get( 'org.glizy', 'modules', array() );
		var_dump( $modules );
	}

}

// shortcut version
/**
 * Class __Modules
 */
class __Modules extends org_glizy_Modules
{
}
