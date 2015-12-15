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
	function &getModulesSorted()
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

	function deleteCache()
	{
		org_glizy_cache_CacheFile::cleanPHP();
	}

	function dump()
	{
		$modules = &org_glizy_ObjectValues::get( 'org.glizy', 'modules', array() );
		var_dump( $modules );
	}

}

/**
 * Class org_glizy_ModuleVO
 */
class org_glizy_ModuleVO
{
	public $id;
	public $name;
	public $description;
	public $classPath;
	public $pageType = '';
	public $model = null;
	public $pluginSnippet = '';
	public $enabled = true;
	public $unique = true;
	public $show = true;
	public $edit = true;
	public $pluginInPageType = false;
	public $pluginInModules = false;
	public $pluginInSearch = false;
	public $canDuplicated = false;
}

/**
 * Class org_glizy_ModuleDescription
 */
class org_glizy_ModuleDescription
{
	private $moduleVO;
	public $name;
	public $author;
	public $authorUrl;
	public $pluginUrl;
	public $version;
	public $description;
	public $installScript;
	public $uninstallScript;

    /**
     * @param org_glizy_ModuleVO $moduleVO
     */
	function __construct( org_glizy_ModuleVO $moduleVO )
	{
		$this->moduleVO = $moduleVO;
		$this->name = $moduleVO->name;

		$path = glz_findClassPath( $moduleVO->classPath );
		if ( !is_null( $path ) && file_exists( $path . '/info.xml' ) )
		{
            /** @var org_glizy_parser_XML $xml */
			$xml = org_glizy_ObjectFactory::createObject( 'org.glizy.parser.XML' );
			$xml->loadAndParseNS( $path . '/info.xml' );
			foreach( $xml->documentElement->childNodes as $n )
			{
				if ( property_exists( $this, $n->tagName ) )
				{
					$this->{ $n->tagName } = utf8_decode( $n->nodeValue );
				}
			}

		}
	}
}

// shortcut version
/**
 * Class __Modules
 */
class __Modules extends org_glizy_Modules
{
}
