<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizy_log_Console extends org_glizy_log_LogBase
{

	function log($msg, $level=GLZ_LOG_DEBUG, $group='')
	{
        if ( is_array( $msg ) || is_object( $msg ) )
        {
            $msg = json_encode($msg);
        }
		echo  $this->_format($msg, $level, $group);
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