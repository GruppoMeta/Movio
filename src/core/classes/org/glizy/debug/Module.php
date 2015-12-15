<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_debug_Module extends GlizyObject
{
    static $debugBar = null;

    static function registerModule()
    {
        if (__Config::get('DEBUG')) {
            include_once('Helpers.php');
            self::$debugBar = org_glizy_ObjectFactory::createObject('org.glizy.debug.GlizyDebugBar');
            org_glizy_ObjectFactory::createObject('org.glizy.debug.Whoops');
        }
    }
}
