<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizy_log_Syslog extends org_glizy_log_LogBase
{
    private $levelMap = [
        GLZ_LOG_DEBUG => LOG_DEBUG,
        GLZ_LOG_INFO => LOG_INFO,
        GLZ_LOG_SYSTEM => LOG_INFO,
        GLZ_LOG_WARNING => LOG_WARNING,
        GLZ_LOG_ERROR => LOG_ERR,
        GLZ_LOG_FATAL => LOG_CRIT,
    ];

    private $name;
    private $logOpts;
    private $logFacility;
    private $tag;
    private $addInfo = [];
    private $forceSyslogLevel;
    private $useJson;


	/**
	 * @param array      $fileName
	 * @param int|string $level
	 * @param string     $group
	 */
	public function __construct($name, $options=[], $level = GLZ_LOG_DEBUG, $group='')
	{
		parent::__construct($options, $level, $group);
        $this->forceMessageToString = false;

        $this->name = strtolower($name).' ';
        $this->logOpts = isset($options['logOption']) ? $options['logOption'] : LOG_PID | LOG_ODELAY;
        $this->logFacility = isset($options['logFacility']) ? $options['logFacility'] : LOG_USER;
        $this->useJson = isset($options['useJson']) ? $options['useJson']==true : false;

        if (isset($options['tag'])) {
            $this->tag = $options['tag'];
        }
        if (isset($options['addInfo'])) {
            $this->addInfo = $options['addInfo'];
        }
        if (isset($options['forceSyslogLevel'])) {
            $this->forceSyslogLevel = $options['forceSyslogLevel'];
        }
	}

	/**
	 * @return void
	 */
	public function __destruct()
	{
		$this->close();
	}

	/**
	 * @param string     $msg
	 * @param int        $level
	 * @param string     $group
	 * @param bool|false $addUserInfo
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function log($msg, $level = GLZ_LOG_DEBUG, $group = '', $addUserInfo = false)
	{
    	if (!$this->_check($level, $group)) {
			return false;
		}

        if (!$this->_isOpen && !$this->open()) {
            return false;
        }

        if ($this->useJson && is_string($msg)) {
            $msg = ['message' => $msg];
        }

        if ( is_array( $msg ) || is_object( $msg ) ) {
            $msg = json_encode(array_merge((array)$msg, (array)$this->addInfo));
        }

        if ($this->tag) {
            $msg = $this->tag.' '.$msg;
        }

		return syslog($this->forceSyslogLevel ? : $this->levelMap[$level], $msg);
	}

	/**
	 * @return bool
	 */
    public function open()
    {
        if (!openlog($this->name, $this->logOpts, $this->logFacility)) {
            throw new Exception('Can\'t open syslog for "'.$this->name.'" and facility "'.$this->logFacility.'"');
        }

        $this->_isOpen = true;
        return true;
    }

	/**
	 * @return bool
	 */
    public function close()
    {
        if ($this->_isOpen) {
            closelog();
        }

        $this->_isOpen = false;
        return true;
    }
}
