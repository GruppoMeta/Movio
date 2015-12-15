<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

/**
 * Class org_glizy_Paths
 */
class org_glizy_Paths
{
    /**
     * @param string $pathApplication
     * @param string $pathCore
     */
	static function init($pathApplication='', $pathCore='')
	{
		$pathsArray 								= &org_glizy_Paths::_getPathsArray();
 		if (count($pathsArray)) {
            return;
        }

        if (empty($pathCore) || $pathCore == '/') {
            $pathCore = './';
        }
        if (substr($pathCore, -1, 1) != '/') {
            $pathCore .= '/';
        }
        if (substr($pathApplication, -1, 1) != '/') {
            $pathApplication .= '/';
        }

		$pathApplicationDepth 						= count(explode('/', $pathApplication))-1;
		$pathsArray['BASE'] 						= $pathCore; //str_repeat('../', $pathApplicationDepth-1);
		$pathsArray['CORE'] 						= $pathCore.'core/';
		$pathsArray['CORE_CLASSES'] 				= $pathCore.'core/classes/';
		$pathsArray['CORE_LIBS'] 					= $pathCore.'core/libs/';
		// $pathsArray['CACHE'] 						= 'cache/';
		$pathsArray['CACHE'] 						= dirname( $pathApplication ) .'/cache/';
		$pathsArray['ROOT'] 						= dirname( $pathApplication ).'/';

		$pathsArray['CACHE_CODE'] 					= $pathsArray['CACHE'];
		$pathsArray['CACHE_IMAGES'] 				= $pathsArray['CACHE'];
		$pathsArray['CACHE_CSS'] 					= $pathsArray['CACHE'];
        $pathsArray['CACHE_JS'] 					= $pathsArray['CACHE'];
		$pathsArray['APPLICATION'] 					= $pathApplication;
		$pathsArray['APPLICATION_CLASSES'] 			= $pathApplication.'classes/';
		$pathsArray['APPLICATION_LIBS'] 			= $pathApplication.'libs/';
		$pathsArray['APPLICATION_MEDIA_ARCHIVE'] 	= $pathApplication.'mediaArchive/';
		$pathsArray['APPLICATION_PAGE_TYPE'] 		= $pathApplication.'pageTypes/';
		$pathsArray['APPLICATION_STARTUP'] 			= $pathApplication.'startup/';
		$pathsArray['APPLICATION_SHUTDOWN'] 		= $pathApplication.'shutdown/';
		$pathsArray['APPLICATION_TEMPLATE'] 		= $pathApplication.'templates/';
		$pathsArray['APPLICATION_STATIC'] 			= $pathsArray['ROOT'].'static/';
		$pathsArray['STATIC_DIR'] 					= $pathsArray['BASE'].'static/';
		$pathsArray['CORE_STATIC_DIR'] 				= $pathsArray['CORE'].'static/js/';

		$page 										= pathinfo(GLZ_SCRIPNAME);
		$dirname									= explode('/', $page['dirname']);
		$page["basename_noext"] 					= substr($page["basename"], 0, strlen($page["basename"])-(strlen($page["extension"])+1));
		$pathsArray['PAGE_FOLDER'] 					= implode('/', array_splice($dirname, count($dirname)-$pathApplicationDepth, $pathApplicationDepth+1));
		$pathsArray['PAGE_INDEX'] 					= $page['basename'];

		$pathsArray['PAGE_AJAX'] 					= $page["basename_noext"].'_ajax.php';
		$pathsArray['PAGE_IMAGEPREVIEW'] 			= $page["basename_noext"].'_imagePreview.php';
		$pathsArray['PAGE_IMAGETHUMBNAIL']			= $page["basename_noext"].'_getImage.php';
		$pathsArray['SEARCH_PATH'] 					= array($pathsArray['APPLICATION_CLASSES'] , $pathsArray['CORE_CLASSES']);
	}


	/**
     * @param $pathCode
     *
     * @return null
     */
    static function get($pathCode)
	{
		$pathsArray = &org_glizy_Paths::_getPathsArray();
		return isset($pathsArray[$pathCode]) ? $pathsArray[$pathCode] : NULL;
	}

	/**
     * @param      $pathCode
     * @param null $fileName
     *
     * @return string
     */
    static function getRealPath($pathCode, $fileName=null)
	{
		$pathsArray = &org_glizy_Paths::_getPathsArray();
		// TODO verificare che il path richiesto esiste veramente
		if (is_null($fileName)) return realpath($pathsArray[$pathCode]).'/';
		else return realpath($pathsArray[$pathCode].'/'.$fileName);
	}


	/**
     * @param $pathCode
     * @param $value
     */
    static function set($pathCode, $value)
	{
		$pathsArray = &org_glizy_Paths::_getPathsArray();
		$pathsArray[$pathCode] = $value;
	}

	/**
     * @param      $pathCode
     * @param      $path
     * @param null $relativeTo
     */
	static function add($pathCode, $path, $relativeTo=null)
	{
        // controlla se l'ultimo carattere è uno slash
        if (substr($path, -1, 1) != "/") {
            $path .= "/";
        }
		$pathsArray 			= &org_glizy_Paths::_getPathsArray();
		$pathsArray[$pathCode]	= is_null($relativeTo) ? $path : $pathsArray[$relativeTo].$path;
	}

	static function dump()
	{
		$pathsArray = &org_glizy_Paths::_getPathsArray();
		var_dump($pathsArray);
	}

	/**
     * @return mixed
     */
    static function getClassSearchPath()
	{
		$pathsArray 				= &org_glizy_Paths::_getPathsArray();
		return $pathsArray['SEARCH_PATH'];
	}

	/**
     * @param $path
     */
    static function addClassSearchPath($path)
	{
		$pathsArray 				= &org_glizy_Paths::_getPathsArray();
		$pathsArray['SEARCH_PATH'][] = $path;
	}

	static function &_getPathsArray()
	{
		// Array associativo (PATH_CODE=>PATH)
		static $_pathsArray = array();
		return $_pathsArray;
	}

    static function destroy()
    {
        $pathsArray = &self::_getPathsArray();
        $pathsArray = array();
    }
}

// shortcut version
/**
 * Class __Paths
 */
class __Paths extends org_glizy_Paths
{
}
