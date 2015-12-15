<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizy_plugins_Search extends org_glizy_plugins_PluginServer
{
	function run($params)
	{
    	$this->runClients($params);

		return $this->getResult();
	}

	function addResult($result)
	{
		$result['__weight__'] = str_pad( $result['__weight__'], 6, "0", STR_PAD_LEFT ).$result['title'];
		$this->_output[] = $result;
	}

	function getResultStructure()
	{
		$result = array();
		$result['title']	= '';
		$result['date']	= '';
		$result['dateOrd']	= '';
		$result['description']	= '';
		$result['__url__'] 		= '';
		$result['__weight__'] 	= 0;
	}
}