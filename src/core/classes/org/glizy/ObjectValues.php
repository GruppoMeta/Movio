<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

/** class org_glizy_ObjectValues */
class org_glizy_ObjectValues
{
    /**
     * @param $className
     * @param string $name
     * @param null $defaultValue
     * @return null
     */
	static function &get($className, $name='', $defaultValue=NULL)
	{
		$name = $name.'@'.$className;
		if ( empty( $className ) ) return $defaultValue;
		$params	= &org_glizy_ObjectValues::_getValuesArray();
		if (!isset($params[$name])) $params[$name] = $defaultValue;
		return $params[$name];
	}

    /**
     * @param $className
     * @param string $name
     * @param $value
     */
	static function set($className, $name='', $value)
	{
		$name = $name.'@'.$className;
		$params	= &org_glizy_ObjectValues::_getValuesArray();
		$params[$name] = $value;
	}

    /**
     * @param $className
     * @param string $name
     * @param $value
     */
	static function setByReference($className, $name='', &$value)
	{
		$name = $name.'@'.$className;
		$params	= &org_glizy_ObjectValues::_getValuesArray();
		$params[$name] = &$value;
	}


    /**
     * @param $className
     * @param string $name
     * @return bool
     */
	function exists($className, $name='')
	{
		$name = $name.'@'.$className;
		$params	= &org_glizy_ObjectValues::_getValuesArray();
		return isset($params[$name]);
	}


    /**
     * @param $className
     * @param string $name
     */
	function remove($className, $name='')
	{
		$name = $name.'@'.$className;
		$params	= &org_glizy_ObjectValues::_getValuesArray();
		if (array_key_exists($name, $params))
		{
			unset($params[$name]);
		}
	}


    static function removeAll()
	{
		$params	= &org_glizy_ObjectValues::_getValuesArray();
		$params = array();
	}

	function dump()
	{
		$params	= &org_glizy_ObjectValues::_getValuesArray();
		var_dump($params);
	}

	function dumpKeys()
	{
		$params	= &org_glizy_ObjectValues::_getValuesArray();
		var_dump(array_keys($params));
	}

    /**
     * @param bool $init
     *
     * @return mixed
     */
    static function &_getValuesArray($init=false)
	{
		static $_valuesArray = array();
		return $_valuesArray;
	}


}

// shortcut version
/**
 * Class __ObjectValues
 */
class __ObjectValues extends org_glizy_ObjectValues
{
}