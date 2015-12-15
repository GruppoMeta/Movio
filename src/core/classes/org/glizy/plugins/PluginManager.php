<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizy_plugins_PluginManager extends GlizyObject
{
	function addPlugin($name, $class)
	{
		$name = str_replace('_', '.', strtolower($name));
		$pluginsInfo = &org_glizy_ObjectValues::get('org.glizy.plugins.PluginManager', 'pluginsInfo', array());
		if (!isset($pluginsInfo[$name]))
		{
			$pluginsInfo[$name] = array();
		}
		$pluginsInfo[$name][] = $class;
	}

	function getPluginChain($name)
	{
		$name = strtolower($name);
		$pluginsInfo = &org_glizy_ObjectValues::get('org.glizy.plugins.PluginManager', 'pluginsInfo', array());
		if (!isset($pluginsInfo[$name]))
		{
			$pluginsInfo[$name] = array();
		}
		return $pluginsInfo[$name];
	}

	static function processPluginChain($name, &$parent, $params)
	{
		$pluginsInfo = org_glizy_plugins_PluginManager::getPluginChain($name);
		foreach ($pluginsInfo as $plugin)
		{
			$pluginObj = &org_glizy_ObjectFactory::createObject($plugin);
			$pluginObj->run($parent, $params);
		}
	}
}