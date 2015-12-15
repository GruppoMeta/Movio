<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizy_log_LiveLog extends org_glizy_log_LogBase
{
	var $_host 	= '127.0.0.1';
	var $_port;
	var $_sock;
	var $_msgsock;
	var $_eol = "\x00";


	function __construct($port, $options=array(), $level=GLZ_LOG_DEBUG, $group='')
	{
		parent::__construct($options, $level, $group);
	//	$this->_host = $host;
		$this->_port = $port;
	}

	function __destruct()
	{
		$this->close();
	}


	function log($msg, $level=GLZ_LOG_DEBUG, $group='')
	{
		if (!$this->_check($level, $group))
		{
			return false;
		}

		/* If the log file isn't already open, open it now. */
        if (!$this->_isOpen && !$this->open()) {
            return false;
        }

        if ( is_array( $msg ) || is_object( $msg ) )
        {
            $msg = json_encode($msg);
        }
		
		if ($msg!='[PAUSE]' &&
			$msg!='[RESTORE]' &&
			$msg!='[CLEAR]' &&
			$msg!='[RESET]' &&
			$msg!='[SETLEVEL]')
		{

			$levelMap = array(GLZ_LOG_DEBUG 	=> '0',
								GLZ_LOG_INFO 	=> '1',
								GLZ_LOG_WARNING => '2',
								GLZ_LOG_ERROR 	=> '3',
								GLZ_LOG_FATAL 	=> '4',
								GLZ_LOG_SYSTEM 	=> '5');

			$message = $levelMap[$level].$this->_format($msg, $level, $group);
		}
		else
		{
			$message = $msg.$this->_eol;
		}

		$ret = socket_write($this->_msgsock, $message, strlen($message));
		return $ret;
	}

    function open()
    {
        if (!$this->_isOpen && function_exists('socket_create'))
		{
			if (($this->_sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) < 0)
			{
				//echo "socket_create() failed: reason: " . socket_strerror($this->_sock) . "\n";
				return false;
			}

			if (($ret = socket_bind($this->_sock, $this->_host, $this->_port)) < 0)
			{
				//echo "socket_bind() failed: reason: " . socket_strerror($ret) . "\n";
				return false;
			}

			if (($ret = socket_listen($this->_sock, 5)) < 0)
			{
				//echo "socket_listen() failed: reason: " . socket_strerror($ret) . "\n";
				return false;
			}

			if (($this->_msgsock = socket_accept($this->_sock)) < 0)
			{
				//echo "socket_accept() failed: reason: " . socket_strerror($msgsock) . "\n";
				return false;
			}

			$this->_isOpen = true;
		}
        return $this->_isOpen;
    }

    function close()
    {
		if ($this->_isOpen && function_exists('socket_create'))
		{
			socket_close($this->_msgsock);
			socket_close($this->_sock);
		}

		$this->_isOpen = false;
        return (!$this->_isOpen);
    }

    function flush()
    {
       return true;
    }
}