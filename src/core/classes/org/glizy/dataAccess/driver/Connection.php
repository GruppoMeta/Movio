<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

/**
 * Class Connection
 */
class Connection extends PDO
{
	public $debug = false;
	private $_errorCode = 0;
	private $_errorInfo = "";
	private $_dsn;

    /**
     * @param       $dsn
     * @param       $username
     * @param       $password
     * @param array $driver_options
     */
	function __construct($dsn, $username, $password, $driver_options = array() )
	{
		$this->_dsn = $dsn;
		parent::__construct($dsn, $username, $password, $driver_options);
	}

    /**
     * @param       $sql
     * @param array $param
     * @param bool  $silent
     *
     * @return bool|DBResultSet
     */
	function execute( $sql, $param = array(), $silent = false )
	{
        $time_start = 0;
		if ( $this->debug ) $time_start = microtime();

		if ( strpos( $this->_dsn, 'sqlite:' ) !== false )
		{
			preg_match_all( '/(CONCAT\(([^\)]*)\))/', $sql, $match );
			if ( count( $match[ 0 ] ) )
			{
				$sql = str_replace( $match[ 0 ], str_replace( ',', ' || ', $match[ 2 ]), $sql );
			}
		}

		// controlla se sono passati dei parametri e se questi sono un array ( es. per clausola IN)
		if ( count( $param ) )
		{
			foreach ($param as $k => $v )
			{
				if ( is_array( $v ) && $v[ 1 ] == 'EXPRESSION' )
				{
					$sql = str_replace( $k, $v[ 0 ], $sql );
					unset( $param[ $k ] );
				} else if (preg_match('/IN\s*?\(\s*?'.$k.'\s*?\)/i', $sql)) {
                    $sql = str_replace( $k, $v, $sql );
                    unset( $param[ $k ] );
				}
			}

		}


		$sth = $this->prepare( $sql );
		if ( count( $param ) )
		{
			if ( strpos( $sql, '?' ) !== false )
			{
				$r = $sth->execute( $param );
			}
			else
			{
				foreach ($param as $k => $v )
				{
					if ( is_array( $v ) )
					{
						$sth->bindValue( $k, $v[ 0 ], $v[ 1 ] );
					}
					else
					{
						$sth->bindValue( $k, $v );
					}
				}

				$r = $sth->execute();
			}
		}
		else
		{
			$r = $sth->execute();
		}

		$this->_errorCode = $sth->errorCode();
		$this->_errorInfo = $sth->errorInfo();


		if ( $this->debug )
		{
			$time_end = microtime();
			$time = $time_end - $time_start;
			echo "[time: ".$time.", ".( $r ? "records: ".$sth->rowCount() : "" )."] ";
			$sql_br = nl2br($sql);
			if ( count( $param ) )
			{
				if ( strpos( $sql_br, '?' ) !== false )
				{
					$sql_br = vsprintf( str_replace( array( "%", "?" ), array( "%%", "'%s'" ), $sql_br ), $param );
				}
				else
				{
					foreach ($param as $k => $v )
					{
						$sql_br = str_replace( $k, is_array( $v ) ? $v[ 0 ] : '"'.$v.'"', $sql_br );
					}
				}
			}

			echo $sql_br;
			echo "<br />\n\r";
		}

		if ( !$r )
		{
			if ( !$this->debug ) {
				// $sql = nl2br($sql);
				if ( count( $param ) )
				{
					if ( strpos( $sql, '?' ) !== false )
					{
						$sql = vsprintf( str_replace( array( "%", "?" ), array( "%%", "'%s'" ), $sql ), $param );
					}
					else
					{
						foreach ($param as $k => $v )
						{
							$sql = str_replace( $k, is_array( $v ) ? $v[ 0 ] : '"'.$v.'"', $sql );
						}
					}
				}
			}

			$eventInfo = array('type' => GLZ_LOG_EVENT,
								'data' => array(
				                    'level' => GLZ_LOG_ERROR,
				                    'group' => 'glizy.sql',
				                    'message' => array('sql' => $sql, 'errorCode' => $this->_errorCode, 'errorInfo' => $this->_errorInfo)
				            ));
            $evt = org_glizy_ObjectFactory::createObject( 'org.glizy.events.Event', $this, $eventInfo );
            org_glizy_events_EventDispatcher::dispatchEvent( $evt );

			if ( !$silent ) echo $this->ErrorMsg()."<br />\n\r";
			return false;
		}
		return new DBResultSet( $sth, $this, strpos( $this->_dsn, 'sqlite:' ) !== false );
	}


    /**
     * @param $str
     * @param $option
     *
     * @return string
     */
	function qstr( $str, $option )
	{
		return '\''.addslashes( $str ).'\'';
	}

	/**
     * @param $str
     *
     * @return array|mixed
     */
    function escape( $str )
    {
        if(is_array($str))
            return array_map(__METHOD__, $str);

        if(!empty($str) && is_string($str)) {
            return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $str);
        }

        return $str;
    }

    /**
     * @return ConnectionQueueExecute
     */
	function getQueueExecute()
	{
		return new ConnectionQueueExecute( $this );
	}

	function SelectDB( $n )
	{
	}

    /**
     * @param       $sql
     * @param       $limitLength
     * @param       $limitStart
     * @param array $params
     *
     * @return bool|DBResultSet
     */
	function selectLimit( $sql, $limitLength, $limitStart, $params = array() )
	{
		return $this->execute( $sql.' LIMIT '.$limitStart.', '.$limitLength, $params );
	}

    /**
     * @return string
     */
	function Insert_ID()
	{
		return $this->lastInsertId();
	}

    /**
     * @return bool|int
     */
	function ErrorNo()
	{
		if ( $this->_errorCode != 0 )
		{
			return $this->_errorCode ;
		}
		else
		{
			return false;
		}
	}

	function ErrorMsg()
	{
		$info = $this->_errorInfo;
		return $info[2];
	}

    /**
     * @param $table
     * @param $param
     *
     * @return array|string
     */
	function MetaColumns( $table, $param )
	{
		if ( strpos( $this->_dsn, 'sqlite:' ) !== false )
		{
			$sql = 'PRAGMA table_info ('.$table.')';
			$stmt = $this->prepare($sql);

			try
			{
                $meta = array();
				if( $stmt->execute() )
				{
					$raw_column_data = $stmt->fetchAll();

					foreach($raw_column_data as $array)
					{
						$dbField = new DBfield;
						$dbField->name = $array['name'];
						$dbField->type = $array['type'];
						$dbField->max_length = -1;
						$dbField->not_null = $array['notnull'] == '1';
						$dbField->has_default = $array['dflt_value'] != null;
						$dbField->default_value = $array['dflt_value'];
						$dbField->primary_key = $array['pk'] == '1';
						$dbField->auto_increment = false;
						$meta[ $array['name'] ] = $dbField;
					}
				}

				return $meta;
			} catch (Exception $e){
				return $e->getMessage(); //return exception
			}
		}
		else
		{
			$sql = 'SHOW COLUMNS FROM ' . $table;
			$stmt = $this->prepare($sql);
            $meta = array();

			if( $stmt->execute() )
			{

				$raw_column_data = $stmt->fetchAll();

				foreach($raw_column_data as $array)
				{
					$dbField = new DBfield;
					$dbField->name = $array['Field'];
					if ( preg_match('/^enum/i', $array['Type'] ) )
					{
						$dbField->type = $array['Type'];

					}
					else
					{
						$dbField->type = preg_replace( '/([^\(]*).*/', '$1', $array['Type'] );
					}
					$dbField->max_length = preg_replace( '/([^\(]*)\((\d*)\).*/', '$2', $array['Type'] );
					if ( $dbField->max_length == $array['Type'] ) $dbField->max_length = -1;
					$dbField->not_null = $array['Null'] == 'NO';
					$dbField->has_default = $array['Default'] != '';
					$dbField->default_value = $array['Default'];
					$dbField->primary_key = $array['Key'] == 'PRI';
					$dbField->auto_increment = $array['Extra'] == 'auto_increment';
					$meta[ $array['Field'] ] = $dbField;
				}
			}

			return $meta;
		}
	}
}

/**
 * Class DBResultSet
 */
class DBResultSet
{
	public $fields;
	public $_currentRow;
	public $EOF;
   	/** @var $sth PDOStatement */
	private $sth;
	private $_count;
	private $_sqlite;
	private $_conn;
	private $buffer;
	private $pos;

    /**
     * @param $sth
     * @param $conn
     * @param $sqlite
     */
	function __construct( $sth, $conn, $sqlite )
	{
		$this->_conn = $conn;
		$this->_sqlite = $sqlite;
		$this->sth = $sth;
		$this->buffer = array();
		$this->pos = 0;
		$this->fetch();
	}

	function MoveFirst()
	{
		$this->pos = 0;
		$this->fetch();
	}


	function MoveNext()
	{
		$this->fetch();
	}

    /**
     * @return mixed
     */
	function RecordCount()
	{
		if ( $this->_sqlite )
		{
			if ( is_null( $this->_count ) )
			{
				$sql = 'select count(*) from ( '.$this->sth->queryString.' )';
                /** @var $sth PDOStatement */
				$sth = $this->_conn->prepare( $sql );
				$sth->execute();
				$row = $sth->fetch(PDO::FETCH_NUM);
				$this->_count = $row[ 0 ];
			}
			return $this->_count;
		}
		else
		{
		return $this->sth->rowCount();
		}
	}

	private function fetch()
	{
		if (  isset( $this->buffer[ $this->pos ] ) )
		{
			$this->fields = $this->buffer[ $this->pos ];
			$this->_currentRow = $this->fields;
			$this->EOF = count( $this->buffer ) === $this->pos;
		}
		else
		{
			$this->fields = $this->sth->fetch(PDO::FETCH_ASSOC);
			$this->_currentRow = $this->fields;
			$this->EOF = $this->_currentRow === false;
			if ( !$this->EOF )
			{
				$this->buffer[ $this->pos ] = $this->fields;
			}
		}

		$this->pos++;
	}

    /**
     * @return int
     */
	public function recordPos()
	{
		return $this->pos;
	}
}

/**
 * Class DBfield
 */
class DBfield
{
	public $name;
	public $max_length;
	public $type;
	public $not_null;
	public $has_default;
	public $default_value;
	public $scale;
	public $primary_key;
	public $auto_increment;
	public $binary = false;
	public $enums;
}

/**
 * Class ConnectionQueueExecute
 */
class ConnectionQueueExecute
{
	public $queryLength = 50000;
    /** @var Connection $_conn */
	private $_conn;
	private $queue = array();
	private $startSql;
	public $decodeFunction = null;

    /**
     * @param $conn
     */
	function __construct( $conn )
	{
		$this->_conn = $conn;
	}

    /**
     * @param $sql
     */
	public function init( $sql )
	{
		$this->startSql = $sql.' ';
	}

    /**
     * @param $values
     */
	public function push( $values )
	{
		$this->queue[] = $values;
	}

    /**
     * @return bool
     */
	public function execute()
	{
		if ( !count( $this->queue ) ) return true;
		$sql = '';
		$part = '';
        $sqlLength = 0;
        $i = 0;
        $result = true;
		while ( true )
		{
			if ( $sql == '' )
			{
				$i = $part == '' ? 0 : 1;
				$sql = $this->startSql.' '.$part;
				$sqlLength = strlen( $sql );
			}
			if ( !count( $this->queue ) ) break;

			$part = array_shift( $this->queue );
			$partLenght = strlen( $part );
			if ( $partLenght + $sqlLength < $this->queryLength )
			{
				$sql .=  ( $i > 0 ? ', ' : '' ).$part;
				$sqlLength += $partLenght + 2;
			}
			else
			{
				if (!$this->fireSql( $sql )) {
                    $result = false;
                }
				$sql = '';
			}
			$i++;
		}
		if (!$this->fireSql( $sql )) {
            $result = false;
        }
        return $result;
	}

    /**
     * @param $sql
     *
     * @return bool|DBResultSet
     */
	private function fireSql( $sql )
	{
		$sql .= ';';
		if ( !is_null( $this->decodeFunction ) )
		{
			$sql = call_user_func( $this->decodeFunction, $sql );
		}
		return $this->_conn->execute( $sql );
	}
}