<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_log_FireBug extends org_glizy_log_LogBase
{
	var $_logFormat 	= '%g %m';
	var $_methodMap = array(	GLZ_LOG_DEBUG 	=> 'console.debug',
								GLZ_LOG_SYSTEM 	=> 'console.info',
								GLZ_LOG_INFO 	=> 'console.info',
								GLZ_LOG_WARNING => 'console.warn',
								GLZ_LOG_ERROR 	=> 'console.error',
								GLZ_LOG_FATAL 	=> 'console.error');
	var $_outputBuffer = '';
	var $logBuffer = '';

	function log($msg, $level=GLZ_LOG_DEBUG, $group='')
	{
        if ( is_array( $msg ) || is_object( $msg ) )
        {
            $msg = json_encode($msg);
        }

		// controlla se il motodo è stato chiamato in modo statico
		if(!method_exists($this, '_check'))
		{
			$l = new org_glizy_log_FireBug(NULL, $level);
			$l->log($msg, $level, $group);
			return;
		}


		if (!$this->_check($level, $group))
		{
			return false;
		}

		$this->_outputBuffer = '';

		if (is_array($msg))
		{
			foreach( $msg as $k => $v )
			{
				$this->_outputBuffer .= $this->_methodMap[$level].'("'.$k.'", '.json_encode( $v ).');';
			}
		}
		else
		{
	        $msg = $this->_format($msg, $level, $group);
			$this->_outputBuffer .= $this->_methodMap[$level].'("'.$this->_cleanText($msg).'");';
		}

		$this->logBuffer .= $this->_outputBuffer.GLZ_COMPILER_NEWLINE2;

		// cerca l'istanza dell'applicazione
		// per inviare l'output al componente root
		$application = org_glizy_ObjectValues::get('org.glizy', 'application');
		if (is_object($application))
		{
			$root = $application->getRootComponent();
			if ( !is_null( $root ) )
			{
				$root->addOutputCode( '<script type="text/javascript">'.$this->logBuffer.'</script>', 'log');
				$this->logBuffer = '';
		}
		}


		return true;
	}

	// i vari metodo sono ridefiniti
	// per permettere di invocare il logger
	// anche con  chiamate statiche
	function debug($msg, $group='')
	{
		if(!method_exists($this, '_check'))
		{
			return org_glizy_log_FireBug::log($msg, GLZ_LOG_DEBUG, $group);
		}
		else
		{
			return $this->log($msg, GLZ_LOG_DEBUG, $group);
		}
	}

	function system($msg, $group='')
	{
		if(!method_exists($this, '_check'))
		{
			return org_glizy_log_FireBug::log($msg, GLZ_LOG_SYSTEM, $group);
		}
		else
		{
			return $this->log($msg, GLZ_LOG_SYSTEM, $group);
		}
	}

	function info($msg, $group='')
	{
		if(!method_exists($this, '_check'))
		{
			return org_glizy_log_FireBug::log($msg, GLZ_LOG_INFO, $group);
		}
		else
		{
			return $this->log($msg, GLZ_LOG_INFO, $group);
		}
	}

	function warning($msg, $group='')
	{
		if(!method_exists($this, '_check'))
		{
			return org_glizy_log_FireBug::log($msg, GLZ_LOG_WARNING, $group);
		}
		else
		{
			return $this->log($msg, GLZ_LOG_WARNING, $group);
		}
	}

	function error($msg, $group='')
	{
		if(!method_exists($this, '_check'))
		{
			return org_glizy_log_FireBug::log($msg, GLZ_LOG_ERROR, $group);
		}
		else
		{
			return $this->log($msg, GLZ_LOG_ERROR, $group);
		}
	}

	function fatal($msg, $group='')
	{
		if(!method_exists($this, '_check'))
		{
			return org_glizy_log_FireBug::log($msg, GLZ_LOG_FATAL, $group);
		}
		else
		{
			return $this->log($msg, GLZ_LOG_FATAL, $group);
		}
	}

	/*private*/ function _cleanText($t)
	{
		$t = str_replace("\r", "", $t);
		$t = str_replace("\n", "\\n", $t);
		$t = str_replace('"', '\\"', $t);
		return $t;
	}
}