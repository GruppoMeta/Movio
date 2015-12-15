<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizycms_views_components_PagePicker extends org_glizy_components_Input
{
	function init()
	{
		// define the custom attributes
        $this->defineAttribute('ajaxController',    false,  'org.glizycms.contents.controllers.autocomplete.ajax.PagePicker',   COMPONENT_TYPE_STRING);
        $this->defineAttribute('type',  false,  '', COMPONENT_TYPE_STRING);
        $this->defineAttribute('protocol',  false,  '', COMPONENT_TYPE_STRING);
        $this->defineAttribute('makeLink',  false,  false, COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('multiple',	false, 	false,	COMPONENT_TYPE_BOOLEAN);
		parent::init();

	}


    function process()
    {
        if (!$this->_application->isAdmin()) {
            $this->_content = $this->_parent->loadContent($this->getId());

            $speakingUrlManager = $this->_application->retrieveProxy('org.glizycms.speakingUrl.Manager');
            $this->_content = $this->getAttribute('makeLink') ?
                                    $speakingUrlManager->makeLink($this->_content) :
                                    $speakingUrlManager->makeUrl($this->_content);
        } else {
            $this->setAttribute('data', ';type=CmsPagePicker;controllername='.$this->getAttribute('ajaxController').
                                        ';filtertype='.$this->getAttribute('type').
                                        ';multiple='.($this->getAttribute('multiple') ? 'true':'false').
                                        ';protocol='.$this->getAttribute('protocol')
                                        , true);
        }
    }
}
