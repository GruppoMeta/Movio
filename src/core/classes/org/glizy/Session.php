<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizy_Session
{
	static function init()
	{
		org_glizy_Session::start();
	}

	static function start()
	{
		if (!org_glizy_Session::isStarted())
		{
            $sessionStore = org_glizy_Config::get('glizy.session.store');
            $prefix = org_glizy_Config::get('SESSION_PREFIX');
            $timeout = org_glizy_Config::get('SESSION_TIMEOUT');

            if ($sessionStore) {
                $storagePrefix = org_glizy_Config::get('glizy.session.store.prefix');
                if (!$storagePrefix) {
                    $storagePrefix = 'PHPSESSID';
                }
                $store = org_glizy_ObjectFactory::createObject($sessionStore, $timeout, $storagePrefix.$prefix);
                if (!$store) {
                    throw new Exception('Session Store don\'t exists: '.$sessionStore);
                }
                session_set_save_handler($store);
            }

			if (!isset($_SESSION))
			{
                org_glizy_Session::glz_session_start();
			}
			if ( isset( $_SESSION[$prefix.'sessionLastAction'] ) && time() - $_SESSION[$prefix.'sessionLastAction'] > $timeout )
			{
				$_SESSION=array();
			}
			$_SESSION[$prefix.'sessionStarted'] = true;
			$_SESSION[$prefix.'sessionLastAction'] = time();
		}
	}

	function stop()
	{
		if (org_glizy_Session::isStarted())
		{
			org_glizy_Session::set('sessionStarted', false);
			session_write_close();
		}
	}

	static function destroy()
	{
		if (org_glizy_Session::isStarted())
		{
            org_glizy_Session::glz_session_start();
			$_SESSION=array();
			session_unset();
			session_destroy();
		}
	}


    /**
     * @return bool
     */
    static function isStarted()
	{
		$prefix = org_glizy_Config::get('SESSION_PREFIX');
		if ( isset($_GET['draft']) && $_GET['draft'] != '' && isset($_GET['sespre']) && $_GET['sespre'] != '' )
		{
			$prefix = $_GET['sespre'];
			org_glizy_Config::set('SESSION_PREFIX', $prefix);
		}
		if (!isset($_SESSION) || !isset($_SESSION[$prefix.'sessionStarted']) || $_SESSION[$prefix.'sessionStarted']!==true)  return false;
		else return true;
	}


	/**
     * @param string     $key
     * @param mixed $defaultValue
     * @param bool $readFromParams
     * @param bool $writeDefaultValue
     * @return mixed
     */
    static function get($key, $defaultValue=NULL, $readFromParams=false, $writeDefaultValue=false)
	{
		org_glizy_Session::start();
		if (!array_key_exists(org_glizy_Config::get('SESSION_PREFIX').$key, $_SESSION))
		{
			$value = ($readFromParams) ? org_glizy_Request::get($key, $defaultValue) : $defaultValue;
			if ($writeDefaultValue) org_glizy_Session::set($key, $value);
		}
		else
		{
			$value = $_SESSION[org_glizy_Config::get('SESSION_PREFIX').$key];
		}
		return $value;
	}

    /**
     * @param $key string
     * @param $value mixed
     */
    static function set($key, $value)
	{
		org_glizy_Session::start();
		$_SESSION[org_glizy_Config::get('SESSION_PREFIX').$key] = $value;
	}


    /**
     * @param $key string
     * @return bool
     */
    static function exists($key)
	{
		org_glizy_Session::start();
		return isset($_SESSION[org_glizy_Config::get('SESSION_PREFIX').$key]);
	}


    /**
     * @param $key string
     */
    static function remove($key)
	{
		org_glizy_Session::start();
		$key = org_glizy_Config::get('SESSION_PREFIX').$key;
		if (array_key_exists($key, $_SESSION))
		{
			unset($_SESSION[$key]);
		}
	}

    static function removeKeysStartingWith($keyPrefix)
    {
		org_glizy_Session::start();
    	$keyPrefix = org_glizy_Config::get('SESSION_PREFIX').$keyPrefix;
        foreach ($_SESSION as $k => $v) {
            if (substr($k, 0, strlen($keyPrefix)) == $keyPrefix) {
		    	unset($_SESSION[$k]);
            }
        }
	}

	static function removeAll()
	{
		org_glizy_Session::destroy();
		org_glizy_Session::start();
	}


    static function getAllAsArray()
	{
		org_glizy_Session::start();
		return $_SESSION;
	}

    /**
     * @param $values array
     */
	function setFromArray($values)
	{
		org_glizy_Session::start();
		foreach($values as $k=>$v)
		{
			$_SESSION[org_glizy_Config::get('SESSION_PREFIX').$k] = $v;
		}
	}

    /**
     * @return string
     */
    static function getSessionId()
	{
		return session_id();
	}


    static function dump()
	{
		org_glizy_Session::start();
		var_dump($_SESSION);
	}

    /**
     * @param bool $init
     * @return mixed
     */
	function &_getValuesArray($init=false)
	{
		if (!$init)
		{
			org_glizy_Session::init();
		}
		return $_SESSION;
	}

    private static function glz_session_start()
    {
        $result = @session_start();
        if (!$result){
            session_regenerate_id(true); // replace the Session ID
            session_start(); // restart the session (since previous start failed)
        }
    }
}

// shortchut version
/**
 * Class __Session
 */
class __Session extends org_glizy_Session
{
}
