<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizy_log_Mail extends org_glizy_log_LogBase
{
	private $mailTitle;
	private $destEmail;

	function __construct($destEmail, $mailTitle, $options=array(), $level=GLZ_LOG_DEBUG, $group='')
	{
		parent::__construct($options, $level, $group);
		$this->destEmail = $destEmail;
		$this->mailTitle = $mailTitle;
	}


	function log($msg, $level=GLZ_LOG_DEBUG, $group='')
	{
		if (!$this->_check($level, $group))
		{
			return false;
		}
        if ( is_array( $msg ) || is_object( $msg ) )
        {
            $msg = json_encode($msg);
        }
		org_glizy_helpers_Mail::sendEmail( 	array('email' => $this->destEmail, 'name' => $this->destEmail ),
											array('email' => org_glizy_Config::get('SMTP_EMAIL'), 'name' => org_glizy_Config::get('SMTP_SENDER')),
											$this->mailTitle,
											str_replace( "\t", "<br />", $this->_format($msg, $level, $group) )
										);
		return true;
	}

    function open()
    {
        return true;
    }

    function close()
    {
        return true;
    }

    function flush()
    {
        return true;
    }
}