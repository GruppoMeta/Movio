<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizy_log_File extends org_glizy_log_LogBase
{
	/*private*/ var $_fileName 	= '';
	/*private*/ var $_append 	= true;
	/*private*/ var $_lock 		= false;
	/*private*/ var $_keepOpen	= true;
	/*private*/ var $_fileResource 		= false;


	function __construct($fileName, $options=array(), $level=GLZ_LOG_DEBUG, $group='')
	{
		parent::__construct($options, $level, $group);
		$this->_fileName = $fileName;
		if (isset($options['append']))
		{
			 $this->_append = $options['append'];
		}
		if (isset($conf['lock']))
		{
            $this->_lock = $options['lock'];
        }
		if (isset($conf['keepOpen']))
		{
            $this->_lock = $options['keepOpen'];
        }
		//$this->log( "----------------- start logging", $level, $group );
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

        if (!$this->_isOpen && !$this->open()) {
            return false;
        }

        if ( is_array( $msg ) || is_object( $msg ) )
        {
            $msg = json_encode($msg);
        }

		if ($this->_lock)
		{
			flock($this->_fileResource, LOCK_EX);
		}

		$ret = (fwrite($this->_fileResource, $this->_format($msg, $level, $group)) !== false);

		if ($this->_lock)
		{
			flock($this->_fileResource, LOCK_UN);
		}

		if (!$this->_keepOpen)
		{
			$this->close();
		}

		return $ret;
	}

    function open()
    {
        if (!$this->_isOpen)
		{
            $this->_fileResource = fopen($this->_fileName, ($this->_append) ? 'a' : 'w');
        }

		$this->_isOpen = $this->_fileResource !== false;
        return $this->_isOpen;
    }

    function close()
    {
        if ($this->_isOpen)
		{
			if (fclose($this->_fileResource)) $this->_fileResource = false;
        }

		$this->_isOpen = $this->_fileResource !== false;
        return (!$this->_isOpen);
    }

    function flush()
    {
        return fflush($this->_fileResource);
    }
}