<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 * 
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

define( 'AR_TYPE_STRING', 'string' );
define( 'AR_TYPE_VERSIONDATE', 'versiondate' );
define( 'AR_TYPE_VERSIONSTATUS', 'versionstatus' );
define( 'AR_TYPE_ENUM', 'enum' );
define( 'AR_TYPE_LANGUAGE', 'language' );
define( 'AR_TYPE_USER', 'user' );
define( 'AR_TYPE_INTEGER', 'integer' );
define( 'AR_TYPE_INT', 'int' );
define( 'AR_TYPE_TEXT', 'text' );
define( 'AR_TYPE_RICHTEXT', 'richtext' );
define( 'AR_TYPE_SITEID', 'siteid' );
define( 'AR_TYPE_DATE', 'date' );
define( 'AR_TYPE_DATETIME', 'datetime' );
define( 'AR_TYPE_ACL', 'acl' );
define( 'AR_TYPE_CATEGORY', 'category' );
define( 'AR_TYPE_VIRTUAL', 'virtual' );

include_once('driver/Connection.php');

class org_glizy_dataAccess_DataAccess
{
    /**
     * @param int $n
     *
     * @return mixed
     * @throws Exception
     */
	static function &getConnection($n=0)
	{
		static $instance = array();
		
		if (!isset($instance['__'.$n])) 
		{
			$options = array();
			$sufix = $n == 0 ? '' : '#'.$n;
			switch ( __Config::get( 'DB_TYPE'.$sufix ) )
			{
				case 'sqlite':
					$dsn = 'sqlite:'.__Paths::getRealPath( 'APPLICATION', 'data/'.__Config::get( 'DB_NAME'.$sufix ) );
					$dbUser = '';
					$dbPassword = '';
					break;
				default:
					$socket = __Config::get( 'DB_SOCKET'.$sufix );
					$socket = empty( $socket ) ? '' : ';unix_socket='.$socket;
					$dsn = 'mysql:dbname='.__Config::get( 'DB_NAME'.$sufix ).';host='.__Config::get( 'DB_HOST'.$sufix ).$socket;
					$dbUser = __Config::get( 'DB_USER'.$sufix );
					$dbPassword = __Config::get( 'DB_PSW'.$sufix );
					if ( __Config::get( 'CHARSET' ) == 'utf-8' )
					{
						$options = array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8");
					}
					if ( __Config::get( 'DB_MYSQL_BUFFERED_QUERY' ) )
					{
						$options[ PDO::MYSQL_ATTR_USE_BUFFERED_QUERY ] = true;
					}
					break;
			}
			$i = 0;
            $lastException = null;
            while (!isset($instance['__' . $n] )) {
                try {
                    if ($i < 3) {
                        $instance['__' . $n] = new Connection($dsn, $dbUser, $dbPassword, $options);
                    } else {
                        break;
                    }
                } catch  (Exception $e) {
                    $i++;
                    $lastException = $e;
                    $eventInfo = array('type' => GLZ_LOG_EVENT,
                                       'data' => array(
                                           'level' => GLZ_LOG_ERROR,
                                           'group' => 'glizy.sql',
                                           'message' => array('errorMessage' => $e->getMessage(), 'errorCode' => $e->getCode(), 'attempt' => $i)
                                       ));
                    $evt = org_glizy_ObjectFactory::createObject( 'org.glizy.events.Event', org_glizy_ObjectValues::get('org.glizy', 'application'), $eventInfo );
                    org_glizy_events_EventDispatcher::dispatchEvent( $evt );
                }
            }
            if (!isset($instance['__' . $n])) {
                throw new Exception($lastException->getMessage(), $lastException->getCode(), $lastException);
            }				
		}

	
		
		return $instance['__'.$n];
	}

    /**
     * @param     $value
     * @param int $n
     *
     * @return mixed
     */	
	static function qstr($value, $n=0)
	{
		$conn = &org_glizy_dataAccess_DataAccess::getConnection($n);
		return $conn->qstr($value, false);
	}
	
    /**
     * @param     $value
     * @param int $n
     *
     * @return mixed
     */
    static function escape($value, $n = 0) {
        $conn = & org_glizy_dataAccess_DataAccess::getConnection($n);

        return $conn->escape($value);
    }

    /**
     * @param int $n
     */	
	static function close($n=0)
	{
		$conn = org_glizy_dataAccess_DataAccess::getConnection($n);
		$conn->close();
	}

    /**
     * @param      $table
     * @param bool $usePrefix
     * @param int  $n
     *
     * @return mixed
     */	
	static function getMetaColumns($table, $usePrefix=true, $n=0)
	{
		$prefix = $usePrefix!=false ? org_glizy_dataAccess_DataAccess::getTablePrefix( $n ) : '';
		$conn = org_glizy_dataAccess_DataAccess::getConnection($n);
		$conn->SelectDB($conn->database);
		return $conn->MetaColumns($prefix.$table, true);
	}

    /**
     * @param int $n
     *
     * @return mixed|null
     */	
	static function getTablePrefix( $n=0 )
	{
		return org_glizy_Config::get( $n== 0 ? 'DB_PREFIX' : 'DB_PREFIX#'.$n);
	}
	
	static function selectDB( $n=0 )
	{
		$conn = org_glizy_dataAccess_DataAccess::getConnection($n);
		$conn->SelectDB( $conn->database );
	}

    /**
     * @param int $n
     *
     * @return mixed
     */
	static function beginTransaction( $n=0 )
	{
		$conn = org_glizy_dataAccess_DataAccess::getConnection($n);
		return $conn->beginTransaction();
	}

    /**
     * @param int $n
     *
     * @return mixed
     */
	static function commit( $n=0 )
	{
		$conn = org_glizy_dataAccess_DataAccess::getConnection($n);
		return $conn->commit();
	}

    /**
     * @param int $n
     *
     * @return mixed
     */
	static function rollBack( $n=0 )
	{
		$conn = org_glizy_dataAccess_DataAccess::getConnection($n);
		return $conn->rollBack();
	}
}
