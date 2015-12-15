<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizy_helpers_JS extends GlizyObject
{
	static function linkCoreJSfile($name, $subDir='', $compress=NULL, $type='text/javascript')
	{
		$url = org_glizy_helpers_JS::getCompressedUrl(org_glizy_Paths::get('CORE_STATIC_DIR').$subDir.$name, $compress);
		$output = '<script type="'.$type.'" src="'.$url.'"></script>';
		return $output;
	}

	static function linkCoreJSfile2($name, $compress=NULL, $type='text/javascript')
	{
		$url = org_glizy_helpers_JS::getCompressedUrl(org_glizy_Paths::get('CORE_STATIC_DIR').$name, $compress);
		$output = '<script type="'.$type.'" src="'.$url.'"></script>';
		return $output;
	}

	static function linkStaticJSfile($name, $compress=NULL, $type='text/javascript')
	{
		$url = org_glizy_helpers_JS::getCompressedUrl(org_glizy_Paths::get('STATIC_DIR').$name, $compress);
		$output = '<script type="'.$type.'" src="'.$url.'"></script>';
		return $output;
	}

	static function linkJSfile($url, $compress=NULL, $type='text/javascript')
	{
		$url = org_glizy_helpers_JS::getCompressedUrl($url, $compress);
		$output = '<script type="'.$type.'" src="'.$url.'"></script>';
		return $output;
	}

	static function JScode($code, $type='text/javascript')
	{
		$output = '<script type="'.$type.'">'.GLZ_COMPILER_NEWLINE2.'// <![CDATA['.GLZ_COMPILER_NEWLINE2.$code.GLZ_COMPILER_NEWLINE2.'// ]]>'.GLZ_COMPILER_NEWLINE2.'</script>';
		return $output;
	}

	function getCompressedUrl($url, $compress=NULL)
	{
		$compress = is_null($compress) ? org_glizy_Config::get('JS_COMPRESS') : $compress;
		$url = $compress ? org_glizy_Paths::get('STATIC_DIR').'js.php?v='.org_glizy_Config::get('APP_VERSION').'&r='.org_glizy_Paths::get('ROOT').'&s='.$url : $url;
		return str_replace('&', '&amp;', $url);

	}
}