<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizy_plugins_PluginServer extends GlizyObject
{
	var $_output = array();


	function runClients($params)
	{
		$className = str_replace('_', '.', $this->getClassName());
		org_glizy_plugins_PluginManager::processPluginChain($className, $this, $params);
	}

	function run($params)
	{
	}

	function getResultStructure()
	{
		$result = array();
		return $result;
	}

	function addResult($result)
	{
		$this->_output[] = $result;
	}

	function getResult()
	{
		return $this->_output;
	}
}