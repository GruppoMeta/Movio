<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizycms_views_components_FormButtonsPanel extends org_glizy_components_ComponentContainer
{
    private $templateStart = '';
    private $templateEnd = '';

    function process()
    {
        $template = __Config::get('glizycms.form.buttonsPanel');
        if ($template) {
            list($templateStart, $templateEnd) = explode('##CONTENT##', $template);
            $this->templateStart = str_replace('##ID##', $this->getid(), $templateStart);
            $this->templateEnd = $templateEnd;
        }
        parent::process();
    }

    function render_html_onStart()
    {
        $this->addOutputCode($this->templateStart);
    }

    function render_html_onEnd()
    {
       $this->addOutputCode($this->templateEnd);
    }
}
