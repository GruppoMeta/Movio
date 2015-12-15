<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

if (!defined('GLZ_REQUEST_ALL')) define('GLZ_REQUEST_ALL', 0);
if (!defined('GLZ_REQUEST_GET')) define('GLZ_REQUEST_GET', 1);
if (!defined('GLZ_REQUEST_POST')) define('GLZ_REQUEST_POST', 2);
if (!defined('GLZ_REQUEST_ROUTING')) define('GLZ_REQUEST_ROUTING', 3);
if (!defined('GLZ_REQUEST_VALUE')) define('GLZ_REQUEST_VALUE', 0);
if (!defined('GLZ_REQUEST_TYPE')) define('GLZ_REQUEST_TYPE', 1);


/**
 * Class org_glizy_Request
 */
class org_glizy_Request
{
	static $decodeUtf8 = false;
	static $skipDecode = false;
	static $translateInfo = true;
	static $method = '';

	function org_glizy_Request()
	{
	}

	static function init()
	{
		self::$method = strtolower( @$_SERVER['REQUEST_METHOD'] );
		$url = '';
		$params	= &org_glizy_Request::_getValuesArray(true);
		$charset = strtolower( org_glizy_Config::get('CHARSET') );
		$requestCharset = @$_SERVER[ 'CONTENT_TYPE' ];
		if ( $charset != "utf-8" && stripos( $requestCharset, 'utf-8' ) !== false )
		{
			self::$decodeUtf8 = true;
		}

		if ( self::$skipDecode )
		{
			self::$decodeUtf8 = false;
		}

		foreach($_GET as $k=>$v)
		{
			if (!is_array($v) && get_magic_quotes_gpc()) $v = stripslashes($v);
			if ( self::$decodeUtf8 ) $v = org_glizy_Request::utf8_decode($v);
			$url .= '&'.$k.'='.$v;
			$params[$k]= array($v, GLZ_REQUEST_GET);
		}

		foreach($_POST as $k=>$v)
		{
			if (!is_array($v) && get_magic_quotes_gpc()) $v = stripslashes($v);
			if ( self::$decodeUtf8 ) $v = org_glizy_Request::utf8_decode($v);
			$url .= '&'.$k.'='.$v;
			$params[$k]= array($v, GLZ_REQUEST_POST);
		}

		$contentType = @$_SERVER[ 'CONTENT_TYPE' ];
		$body = @file_get_contents('php://input');
		if ( $body && $contentType && $contentType != 'application/x-www-form-urlencoded' )
		{
			$params['__postBody__'] = array($body, GLZ_REQUEST_POST);
			parse_str( $body, $output );
			foreach($output as $k=>$v)
			{
				if ( !isset( $params[ $k ] ) )
				{
					$url .= '&'.$k.'='.$v;
					$params[$k]= array($v, GLZ_REQUEST_POST);
				}
			}
		}

		$params[ '__url__' ] = array( __Routing::$requestUrl, GLZ_REQUEST_GET );
		$params[ '__back__url__' ] = array( $url, GLZ_REQUEST_GET );

		if ( self::$translateInfo && isset($params[ 'pageId' ]))
		{
			$pageId = strtolower( $params[ 'pageId' ][ GLZ_REQUEST_VALUE ] );
			$translateInfo = __Session::get( '__translateInfo_'.$pageId, array( ) );

			foreach( $translateInfo as $v )
			{
				if ( isset($params[ $v[ 'target_name' ] ]) && $params[ $v[ 'target_name' ] ][ GLZ_REQUEST_VALUE ] == $v[ 'label' ] )
				{
					$params[ $v[ 'target' ] ][ GLZ_REQUEST_VALUE ] =  $v[ 'value' ];
				}
			}
			__Session::remove( '__translateInfo_'.$pageId );
		}

		$values = __Session::get( '__valuesForNextRefresh' );
		if ( isset( $values ) && is_array( $values ) )
		{
			foreach( $values as $k => $v )
			{
				$params[ $k  ][ GLZ_REQUEST_VALUE ] = $v;
			}
			__Session::remove( '__valuesForNextRefresh' );
		}
	}

    static function get($name, $defaultValue=NULL, $type=GLZ_REQUEST_ALL)
	{
		$params	= &org_glizy_Request::_getValuesArray();
		$value = array_key_exists($name, $params) ? $params[$name] : NULL;
		$value = is_null($value) ? $defaultValue : ($type==GLZ_REQUEST_ALL || $type==$value[GLZ_REQUEST_TYPE] ? $value[GLZ_REQUEST_VALUE] : $defaultValue);
		return $value;
	}

	static function getParams()
	{
		$params	= &org_glizy_Request::_getValuesArray();
		return isset($params[ '__params__' ]) ? $params[ '__params__' ] : array();
	}


    static function set($name, $value, $type=GLZ_REQUEST_ALL)
	{
		$params	= &org_glizy_Request::_getValuesArray();

		$params[$name] = array($value, $type);
	}

	function add($name, $value, $type=GLZ_REQUEST_ALL)
	{
		$params	= &org_glizy_Request::_getValuesArray();
		if (isset($params[$name]))
		{
			trigger_error('The param is already set');
		}

		$params[$name] = array($value, $type);
	}

	static function exists($key, $type=null)
	{
		$params	= &org_glizy_Request::_getValuesArray();
		if (is_null($type) || $type==GLZ_REQUEST_ALL) {
			return isset($params[$key]);
		} else {
			return isset($params[$key]) && $params[$key][GLZ_REQUEST_TYPE] == $type;
		}
	}

	function isEqual( $name, $value )
	{
		return strtolower( org_glizy_Request::get( $name, '' ) ) == strtolower( $value );
	}

	static function remove($name)
	{
		$params	= &org_glizy_Request::_getValuesArray();
		if (isset($params[$name]))
		{
			unset($params[$name]);
		}
	}

	static function removeAll()
	{
		$params	= &org_glizy_Request::_getValuesArray();
		$params = array();
	}

	static function getAllAsArray()
	{
		$params	= &org_glizy_Request::_getValuesArray();
		$result = array();
		foreach($params	as $k=>$v)
		{
			$result[$k] = $v[GLZ_REQUEST_VALUE];
		}
		return $result;
	}

	static function setFromArray($values, $type=GLZ_REQUEST_ALL)
	{
		$params	= &org_glizy_Request::_getValuesArray( true );
		foreach($values as $k=>$v)
		{
			$params[$k] = is_array($v) ? $v : array($v, $type);
		}

	}


	function setValuesForNextRefresh( $values )
	{
		__Session::set( '__valuesForNextRefresh', $values );
	}


	static function dump($name=null)
	{
	    if ($name) {
    		var_dump(org_glizy_Request::get($name));
	    } else {
    		$params	= &org_glizy_Request::_getValuesArray();
    		var_dump($params);
	    }
	}

	static function &_getValuesArray($init=false)
	{
		static $_valuesArray = array();
		if (!count($_valuesArray) && !$init)
		{
			org_glizy_Request::init();
		}
		return $_valuesArray;
	}

	function utf8_decode($values)
	{
		if (is_array($values))
		{
			$keys = array_keys($values);
			$count = count($values);
			for ($i = 0; $i < $count; $i++)
			{
				if (is_array($values[$keys[$i]]))
				{
					$values[$keys[$i]] = org_glizy_Request::utf8_decode($values[$keys[$i]]);
				}
				else
				{
					if ( function_exists('iconv') )
					{
						$output[$keys[$i]] = iconv("UTF-8", "CP1252", $values[$keys[$i]]);
					}
					else
					{
						$output[$keys[$i]] = utf8_decode($values[$keys[$i]]);
					}
				}
			}
			return $values;
		}
		else
		{
			if( function_exists('iconv') )
			{
				return iconv( "UTF-8", "CP1252", $values );
			}
			else
			{
				return utf8_decode( $values );
			}
		}
	}

    static function destroy()
    {
        $valuesArray = &self::_getValuesArray();
        $valuesArray = array();
    }
}

// shortcut version
/**
 * Class __Request
 */
class __Request extends org_glizy_Request
{
}