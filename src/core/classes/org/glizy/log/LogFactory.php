<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizy_log_LogFactory
{
    /**
     * @return org_glizy_log_LogBase
     */
    static function &create()
	{
		$args = func_get_args();
		$name = array_shift($args);
		$newObj = NULL;

		if (file_exists(dirname(__FILE__).'/'.$name.'.php'))
		{
			glz_import('org.glizy.log.'.$name);
			$className = str_replace('.', '_', 'org.glizy.log.'.$name);
			$costructString = '$newObj = new '.$className.'(';
			for ($i=0; $i<count($args); $i++)
			{
				$costructString .= '$args['.$i.']';
				if ($i<count($args)-1) $costructString .= ', ';
			}
			$costructString .= ');';
			eval($costructString);
		}
		return $newObj;
	}
}