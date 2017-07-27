<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

use DebugBar\StandardDebugBar;

class org_glizy_debug_views_components_DebugBar extends org_glizy_components_Component
{
    private $debugbarRenderer;

    function __construct(&$application, &$parent, $tagName='', $id='', $originalId='')
    {
        parent::__construct($application, $parent, $tagName, $id, $originalId);
        $debugBar = org_glizy_debug_Module::$debugBar;

        $this->debugbarRenderer = $debugBar->getJavascriptRenderer(GLZ_HOST.'/'.__Paths::get('CORE').'libs/DebugBar/Resources/');
        $this->debugbarRenderer->setIncludeVendors(false);
    }


	public function render($mode)
	{
        $this->addOutputCode($this->debugbarRenderer->renderHead(), 'head');
        $this->addOutputCode($this->debugbarRenderer->render(), 'tail');
	}

}
