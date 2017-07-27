<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizy_exceptions_InterfaceException extends Exception
{
    public static function notImplemented($interfaceName, $className)
    {
        return new self('Interface '.$interfaceName.' not implemented in class '.$className);
    }
}