<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

define('GLZ_SESSION_EX_VOLATILE', 1);
define('GLZ_SESSION_EX_PERSISTENT', 2);
define('GLZ_SESSION_EX_PREFIX', 'session_ex');

class org_glizy_SessionEx extends GlizyObject
{
	var $_pageId;
	var $_componentId;
	var $_values;
	var $_allValues;

	function __construct($componentId)
	{
		org_glizy_Session::init();

		$this->_pageId 		= strtolower(org_glizy_ObjectValues::get('org.glizy.application', 'pageId'));

		$this->_componentId = $componentId;
		$this->_allValues	= org_glizy_Session::get(GLZ_SESSION_EX_PREFIX, array(), false, true);

		if (!array_key_exists($this->_pageId, $this->_allValues))
		{
			$this->_allValues[$this->_pageId] = array();
		}

		foreach($this->_allValues as $k=>$v)
		{
			if ($k!=$this->_pageId)
			{
				foreach($v as $kk=>$vv)
				{
					if ($vv['type']!=GLZ_SESSION_EX_PERSISTENT)
					{
						unset($this->_allValues[$k][$kk]);
					}
				}
			}
		}

		$this->_values = &$this->_allValues[$this->_pageId];
		org_glizy_Session::set(GLZ_SESSION_EX_PREFIX, $this->_allValues);

		/*
		da verificare
		$url = org_glizy_Request::get( '__back__url__' );
		$userted = array();
		if ( !empty( $url ) )
		{
			foreach($this->_values as $k=>$v)
			{
				$val = explode( "_", $k );
				if ( count( $val ) == 2 && !in_array( $val[1], $userted) )
				{
					$userted[] = $val[1];
					if ( strpos( $url, $val[1].'='.$v['value'] ) === false )
					{
						$url .= '&'.$val[1].'='.$v['value'];
					}
				}
			}
			org_glizy_Request::set( '__back__url__', $url );
		}
		*/
	}

	function get($name, $defaultValue=NULL, $readFromParams=false, $writeDefaultValue=false)
	{
		$origName = $name;
		$name = $this->_componentId.'_'.$name;
		if (!array_key_exists($name, $this->_values))
		{
			// TODO
			// verificare se � meglio aggiungere $this->_pageId quando si legge dai parametri della pagian
			$value = ($readFromParams) ? org_glizy_Request::get($name, $defaultValue) : $defaultValue;

			if ($writeDefaultValue!==false) $this->set($origName, $value, GLZ_SESSION_EX_VOLATILE);
		}
		else
		{
			$value = ($readFromParams) ? org_glizy_Request::get($name, $this->_values[$name]['value']) : $this->_values[$name]['value'];
		}
		return $value;
	}


	function set($name, $value, $type=GLZ_SESSION_EX_VOLATILE)
	{
		$name = $this->_componentId.'_'.$name;
		if (!array_key_exists($name, $this->_values))
		{
			$tempValue 				= array();
			$tempValue['value'] 	= $value;
			$tempValue['type'] 		= $type;
			$this->_values[$name] 	= $tempValue;
		}
		else
		{
			$this->_values[$name]['value']	= $value;
		}

		org_glizy_Session::set(GLZ_SESSION_EX_PREFIX, $this->_allValues);
	}

	function exists($name)
	{
		$name = $this->_componentId.'_'.$name;
		return isset($this->_values);
	}


	function remove($name)
	{
		$name = $this->_componentId.'_'.$name;
		if (array_key_exists($name, $this->_values))
		{
			unset($this->_values[$name]);
		}
	}


	function removeAll()
	{
		$this->_values = array();
	}


	function getAllAsArray()
	{
		$tempValues = array();
		foreach($this->_values as $k=>$v)
		{
			$tempValues[$k] = $v['value'];
		}
		return $tempValues;
	}

	function setFromArray($values, $type=GLZ_SESSION_EX_VOLATILE)
	{
		foreach($values as $k=>$v)
		{
			$this->_values[$k] = array('value' => $v, 'type' => $type);
		}
	}

	function getSessionId()
	{
		return session_id();
	}


	function dump()
	{
		var_dump($this->_values);
	}
}