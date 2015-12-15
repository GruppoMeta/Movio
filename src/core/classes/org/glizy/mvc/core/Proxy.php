<?php

/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */
class org_glizy_mvc_core_Proxy extends GlizyObject
{

    /**
     * @var org_glizy_mvc_core_Application $application
     */
    protected $application = null;

    /**
     * @param org_glizy_application_Application $application
     */
    function __construct($application)
    {
        $this->application = $application;
    }
}