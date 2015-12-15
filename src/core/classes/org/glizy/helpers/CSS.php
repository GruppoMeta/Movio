<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizy_helpers_CSS extends GlizyObject
{
	static function linkCoreCSSfile($name, $subDir='')
	{
		$output = '<link rel="stylesheet" type="text/css" media="all" href="'.org_glizy_Paths::get('CORE_STATIC_DIR').'assets/css/'.$subDir.$name.'" />';
		return $output;
	}

	static function linkCoreCSSfile2($name)
	{
		$output = '<link rel="stylesheet" type="text/css" media="all" href="'.org_glizy_Paths::get('CORE_STATIC_DIR').$name.'" />';
		return $output;
	}

	static function linkStaticCSSfile($name)
	{
		$output = '<link rel="stylesheet" type="text/css" media="all" href="'.org_glizy_Paths::get('STATIC_DIR').$name.'" />';
		return $output;
	}

	static function linkCSSfile($name, $media='all')
	{
		if ($media)  {
			$mediaAttr = 'media="'.$media.'"';
		}
		$output = '<link rel="stylesheet" type="text/css" '.$mediaAttr.' href="'.$name.'" />';
		return $output;
	}

    static function CSScode($code)
    {
		$output = '<style type="text/css">'.$code.'</style>';
		return $output;
	}
}