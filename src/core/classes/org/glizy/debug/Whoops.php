<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_debug_Whoops extends GlizyObject
{


    public function __construct()
    {
        spl_autoload_register(array($this, 'loadClass'));

        GlizyErrorHandler::unregister();

        $whoops = new \Whoops\Run;
        $handler = new \Whoops\Handler\PrettyPageHandler;

        $whoops->pushHandler($handler);
        $whoops->register();
    }

    public function loadClass($className)
    {
        if (strpos($className, 'Whoops') === 0) {
            require_once(__DIR__.'/../../../../libs/'.str_replace('\\', '/', $className).'.php');
            return true;
        }
    }
}


