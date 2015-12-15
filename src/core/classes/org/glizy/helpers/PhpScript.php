<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizy_helpers_PhpScript
{
	static function parse($phpcode)
	{
		$phpcode = preg_replace("/\{php\:(.*)\}/i", "$1", $phpcode);
		$phpcode = str_replace('.',				'->',	$phpcode);
		$phpcode = str_replace('->->',			'.',	$phpcode);
		$phpcode = preg_replace('/\bnot\b/i', 	' !',	$phpcode);
		$phpcode = preg_replace('/\bne\b/i', 	' != ',	$phpcode);
		$phpcode = preg_replace('/\band\b/i', 	' && ',	$phpcode);
		$phpcode = preg_replace('/\bor\b/i', 	' || ',	$phpcode);
		$phpcode = preg_replace('/\blt\b/i', 	' < ', 	$phpcode);
		$phpcode = preg_replace('/\bgt\b/i', 	' > ', 	$phpcode);
		$phpcode = preg_replace('/\bge\b/i', 	' >= ',	$phpcode);
		$phpcode = preg_replace('/\ble\b/i', 	' <= ',	$phpcode);
		$phpcode = preg_replace('/\beq\b/i', 	' == ',	$phpcode);
		if (substr($phpcode,-1,1)!=';') $phpcode .= ';';
		if ('return '!= substr($phpcode,0,7)) $phpcode = 'return '.$phpcode;
		$phpcode = '$application = &org_glizy_ObjectValues::get(\'org.glizy\', \'application\'); $user = &$application->getCurrentUser(); $menu = &$application->getCurrentMenu();'.$phpcode;
		return $phpcode;
	}

	static function callMethodWithParams( $actionClass, $method, $callParams=null )
	{
		if ( is_object( $actionClass ) )
		{
			$reflectionClass = new ReflectionClass( $actionClass );
			$params = __Request::getParams();
			if ( $reflectionClass->hasMethod( $method ) )
			{
				$reflectionMethod = $reflectionClass->getMethod( $method );
				$methodParams = $reflectionMethod->getParameters();
				$params = array();
				foreach( $methodParams as $v )
				{
					$params[] = is_array($callParams) && isset($callParams[$v->name]) ? $callParams[$v->name] : __Request::get( $v->name );
				}
				return call_user_func_array( array( $actionClass, $method ), $params );
			}
		}

		return false;
	}
}