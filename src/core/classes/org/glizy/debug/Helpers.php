<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */



class __DebugBar
{
    static function debug($message)
    {
        org_glizy_debug_Module::$debugBar->addMesage($message, 'debug');
    }

    static function info($message)
    {
        org_glizy_debug_Module::$debugBar->addMesage($message, 'info');
    }

    static function warning($message)
    {
        org_glizy_debug_Module::$debugBar->addMesage($message, 'warning');
    }

    static function error($message)
    {
        org_glizy_debug_Module::$debugBar->addMesage($message, 'error');
    }

    static function fatal($message)
    {
        org_glizy_debug_Module::$debugBar->addMesage($message, 'critical');
    }

    static function startMeasure($name)
    {
        org_glizy_debug_Module::$debugBar->getTime()->startMeasure($name);
    }

    static function stopMeasure($name)
    {
        org_glizy_debug_Module::$debugBar->getTime()->stopMeasure($name);
    }

}