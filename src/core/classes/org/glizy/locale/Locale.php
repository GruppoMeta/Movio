<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizy_locale_Locale extends GlizyObject
{

    /**
     * @return mixed|string
     */
	static function get()
	{
		$args = func_get_args();
		$code = array_shift($args);
		$values = &org_glizy_ObjectValues::get('org.glizy.locale.Locale');
		if (!isset($values[$code]))
		{
			return glz_encodeOutput( $code );
		}
		if (is_array($values[$code])) return $values[$code];
		if (strpos($values[$code], '<')!==false) return vsprintf($values[$code], $args);

		return glz_encodeOutput(vsprintf($values[$code], $args));
	}

    /**
     * @return mixed|string
     */
	static function getPlain()
	{
		$args = func_get_args();
		$code = array_shift($args);
		$values = &org_glizy_ObjectValues::get('org.glizy.locale.Locale');
		if (!isset($values[$code]))
		{
			return $code;
		}
		if (is_array($values[$code])) return $values[$code];
		if (strpos($values[$code], '<')!==false) return vsprintf($values[$code], $args);

		return vsprintf($values[$code], $args);
	}

    /**
     * @param array $newValues
     */
	static function append($newValues)
	{
		$values = &org_glizy_ObjectValues::get('org.glizy.locale.Locale', '', array());
		$values = array_merge($values, $newValues);
	}

}

/* shortcut */
/**
 * @return mixed
 */
function __T()
{
	$l = new org_glizy_locale_Locale();
	$args = func_get_args();
	return call_user_func_array(array($l, 'get'), $args);
}

/**
 * @return mixed
 */
function __Tp()
{
	$l = new org_glizy_locale_Locale();
	$args = func_get_args();
	return call_user_func_array(array($l, 'getPlain'), $args);
}