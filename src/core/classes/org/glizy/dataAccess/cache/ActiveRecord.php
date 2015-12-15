<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_dataAccess_cache_ActiveRecord extends GlizyObject
{
    private $data = NULL;

    function __construct($data)
    {
        $this->data = $data;
    }

    function getValuesAsArray()
    {
        return $this->data;
    }

    public function __get($name)
    {
        return @$this->data[$name];
    }
}