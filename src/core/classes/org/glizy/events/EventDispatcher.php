<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

/** class org_glizy_events_EventDispatcher */
class org_glizy_events_EventDispatcher
{

	static function addEventListener($type, &$listener, $useCapture=false, $method=null )
	{
		$type = strtolower( $type );
		$eventsChain = &org_glizy_ObjectValues::get('org.glizy.events.EventTarget', 'events', array());
		if (!isset($eventsChain[$type]))
		{
			$eventsChain[$type] = array();
		}
		$eventsChain[$type][] = array('listener' => &$listener, 'useCapture' => $useCapture, 'method' => $method);
	}

	static function removeEventListener($type, &$listener, $useCapture=false)
	{
		// TODO
	}

	static function dispatchEvent(&$evt)
	{
		$eventsChain = &org_glizy_ObjectValues::get('org.glizy.events.EventTarget', 'events', array());
		if (isset($eventsChain[$evt->type]))
		{
			for($i=0; $i<count($eventsChain[$evt->type]); $i++)
			{
				$listener = $eventsChain[$evt->type][$i];
				$evt->setCurrentTarget($listener['listener']);
				$method = str_replace(array('@', '.'), '_', $evt->type);
				if (method_exists($listener['listener'], $method))
				{
					$listener['listener']->{$method}($evt);
				}
				else if ( !is_null( $listener['method'] ) && method_exists($listener['listener'], $listener['method'] ) )
				{
					$listener['listener']->{ $listener['method'] }($evt);
				}
			}
		}

		// TODO
		return true;
	}

	static function hasEventListener($type)
	{
		// TODO
		return true;
	}

	static function willTrigger($type)
	{
		// TODO
		return true;
	}
}