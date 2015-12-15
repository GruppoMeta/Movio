<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

define( 'GLZ_MESSAGE_SUCCESS', 'SUCCESS' );
define( 'GLZ_MESSAGE_FAULT', 'FAULT' );
define( 'GLZ_MESSAGE_ERROR', 'ERROR' );

class org_glizy_application_MessageStack
{
	static function add($message, $type=GLZ_MESSAGE_SUCCESS)
	{
		$messages = &org_glizy_Session::get('org.glizy.application.MessageStack',  array());
		if (!isset($messages[$type]))
		{
			$messages[$type] = array();
		}
		$messages[$type][] = $message;
		org_glizy_Session::set('org.glizy.application.MessageStack',  $messages );
	}

	static function get($type=NULL)
	{
		$messages = &org_glizy_Session::get('org.glizy.application.MessageStack',  array());
		if (is_null($type) || $type=='ALL')
		{
			$tempMessages = array();
			foreach( $messages as $k=>$v )
			{
				$tempMessages = array_merge( $tempMessages, $v );
			}
			return $tempMessages;
		}
		else
		{
			return isset($messages[$type]) ? $messages[$type] : array();
		}
	}

	static function reset($type=NULL)
	{
		$messages = &org_glizy_Session::get('org.glizy.application.MessageStack',  array());
		if (is_null($type) || $type=='ALL')
		{
			$messages = array();
		}
		else
		{
			$messages[$type] = array();
		}
		org_glizy_Session::set('org.glizy.application.MessageStack',  $messages );
	}
}