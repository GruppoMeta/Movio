<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

/** class org_glizy_Registry */
class org_glizy_Registry
{
    /**
     * @param $path
     * @param null $defaultValue
     * @return null
     */
    static function get($path, $defaultValue=NULL)
	{
		$params	= &org_glizy_Registry::_getValuesArray();

		if (array_key_exists($path, $params)) return $params[$path];
		else
		{
			$rs = org_glizy_ObjectFactory::createModel('org.glizy.models.Registry');
			$rs->registry_path = $path;
			if ($rs->find())
			{
				$params[$path] = $rs->registry_value;
				return $rs->registry_value;
			}
			else return $defaultValue;
		}
	}

	static function set($path, $value)
	{
		$params	= &org_glizy_Registry::_getValuesArray();
		$params[$path] = $value;
		$rs = org_glizy_ObjectFactory::createModel('org.glizy.models.Registry');
		$rs->find(array('registry_path' => $path));
		$rs->registry_path 	= $path;
		$rs->registry_value = $value;
		$rs->save();
	}

	static function add($path, $value)
	{
		self::set($path, $value);
	}

	static function exists($key)
	{
		// TODO
	}

	static function remove($path)
	{
		$params	= &org_glizy_Registry::_getValuesArray();

		if (array_key_exists($path, $params)) unset($params[$path]);
		$rs = org_glizy_ObjectFactory::createModel('org.glizy.models.Registry');
		$rs->registry_path = $path;
		if ($rs->find()) $rs->delete();
	}

	static function query($path)
	{
		$params	= &org_glizy_Registry::_getValuesArray();
		$iterator = org_glizy_ObjectFactory::createModelIterator('org.glizy.models.Registry', 'all', array('filters' => array('registry_path' => $path)));

		// TODO controlare se ci sono stati errori
		$result = array();

		foreach ($iterator as $ar)
		{
			$params[$ar->registry_path] = $ar->registry_value;
			$result[$ar->registry_path] = $ar->registry_value;
		}

		return $result;
	}

	static function &_getValuesArray($init=false)
	{
		static $_valuesArray = array();
		return $_valuesArray;
	}
}

// shortchut version
class __Registry extends org_glizy_Registry
{
}