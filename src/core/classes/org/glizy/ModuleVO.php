<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

/**
 * Class org_glizy_ModuleVO
 */
class org_glizy_ModuleVO
{
    public $id;
    public $name;
    public $description;
    public $classPath;
    public $pageType = '';
    public $model = null;
    public $pluginSnippet = '';
    public $enabled = true;
    public $unique = true;
    public $show = true;
    public $edit = true;
    public $pluginInPageType = false;
    public $pluginInModules = false;
    public $pluginInSearch = false;
    public $canDuplicated = false;
    public $path = false;
}

