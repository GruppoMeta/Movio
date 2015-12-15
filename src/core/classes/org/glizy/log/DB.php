<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizy_log_DB extends org_glizy_log_LogBase
{
	/*private*/ var $_ar;
	/*private*/ var $application;
	/*private*/ var $_append 	= true;
	/*private*/ var $_lock 		= false;
	/*private*/ var $_keepOpen	= true;
	/*private*/ var $_fileResource 		= false;


	function __construct($options=array(), $level=GLZ_LOG_DEBUG, $group='')
	{
		parent::__construct($options, $level, $group);

		$this->_ar = org_glizy_ObjectFactory::createModel( 'org.glizy.models.Log' );
		$this->application = org_glizy_ObjectValues::get('org.glizy', 'application');

		// TODO
		//$this->_ar->enableQueue();
	}

	function __destruct()
	{
		// TODO
		//$this->_ar->executeQueue();
	}

	function log($msg, $level=GLZ_LOG_DEBUG, $group='')
	{
		if (!$this->_check($level, $group))
		{
			return false;
		}

		$this->_ar->emptyRecord();
		$this->_ar->log_level = (string)$level;
		// TODO
		$this->_ar->log_date = new org_glizy_types_DateTime();
		$this->_ar->log_ip = $_SERVER["REMOTE_ADDR"];
		$this->_ar->log_session = session_id();
		$this->_ar->log_group = $group;
		$this->_ar->log_message = $msg;
		$this->_ar->log_FK_user_id = $this->application->getCurrentUser()->id;
		return $this->_ar->save();
	}
}