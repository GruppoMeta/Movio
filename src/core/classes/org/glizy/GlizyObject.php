<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

/** class GlyzyObject */
class GlizyObject
{
	protected $_className = NULL;

    /**
     *
     */
	function __construct()
	{
	}

    /**
     * @param bool $toLower
     *
     * @return null|string
     */
	function getClassName($toLower=true)
	{
		if ($toLower) {
			return strtolower( is_null($this->_className) ? get_class($this) : $this->_className );
		} else {
			return is_null($this->_className) ? get_class($this) : $this->_className;
		}
	}

	// events functions
    /**
     * @param $type
     * @param $listener
     * @param bool $useCapture
     * @param null $method
     */
	function addEventListener($type, &$listener, $useCapture=false, $method=null )
	{
		org_glizy_events_EventDispatcher::addEventListener($type, $listener, $useCapture, $method );
	}

    /**
     * @param $type
     * @param $listener
     * @param bool $useCapture
     */
	function removeEventListener($type, &$listener, $useCapture=false)
	{
		org_glizy_events_EventDispatcher::removeEventListener($type, $listener, $useCapture);
	}

    /**
     * @param $evt
     * @return bool
     */
	function dispatchEvent(&$evt)
	{
		if (is_array($evt))
		{
			$evt = &org_glizy_ObjectFactory::createObject('org.glizy.events.Event', $this, $evt);
		}
		return org_glizy_events_EventDispatcher::dispatchEvent($evt);
	}

    /**
     * @param $type
     * @param $evt
     * @return bool
     */
	function dispatchEventByArray($type, $evt)
	{
		$eventInfo = array('type' => $type, 'data' => $evt);
		$evt = &org_glizy_ObjectFactory::createObject('org.glizy.events.Event', $this, $eventInfo);
		return org_glizy_events_EventDispatcher::dispatchEvent($evt);
	}

    /**
     * @param $type
     * @return bool
     */
	function hasEventListener($type)
	{
		return org_glizy_events_EventDispatcher::hasEventListener($type);
	}

    /**
     * @param $type
     * @return bool
     */
	function willTrigger($type)
	{
		return org_glizy_events_EventDispatcher::willTrigger($type);
	}

	// error methods
    /**
     * @param $msg
     */
	function triggerError($msg)
	{
		trigger_error($msg);
	}

    /**
     * @param $msg
     * @param int $level
     * @param string $group
     * @param bool $addUserInfo
     */
	function log( $msg, $level = GLZ_LOG_DEBUG, $group = '', $addUserInfo = false )
	{
		if ( $addUserInfo )
		{
            /** @var org_glizy_application_User $user */
			$user = &org_glizy_ObjectValues::get('org.glizy', 'user');
            if ( is_string( $msg ) )
            {
                $msg .= "\t" . $user->toString();
            }
            if ( is_array( $msg ) )
            {
                $msg['user'] = $user->toString();
            }
            if ( is_object( $msg ) )
            {
                $msg->user = $user->toString();
            }
		}
		$this->dispatchEventByArray( GLZ_LOG_EVENT, array('level' => $level,
			'group' => $group,
			'message' => is_string( $msg ) ? $this->getClassName()." ".$msg : $msg ) );
	}

    /**
     * @param $msg
     * @param string $debugInfo
     * @param bool $type
     * @param string $group
     * @param bool $addUserInfo
     */
	function logAndMessage( $msg, $debugInfo = '', $type=false, $group = '', $addUserInfo = false )
	{
        $type = $type===false || $type===true ?  ($type ? GLZ_LOG_ERROR : GLZ_LOG_DEBUG) : $type;
		if ( class_exists( 'org_glizy_application_MessageStack' ) )
		{
			org_glizy_application_MessageStack::add( $msg, $type==GLZ_LOG_ERROR ? GLZ_MESSAGE_ERROR : GLZ_MESSAGE_SUCCESS );
		}
		$this->log( $msg.' '.$debugInfo, $type, $group, $addUserInfo );
	}
}
