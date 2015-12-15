<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2011 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizycms_template_views_components_SelectTemplate extends org_glizy_components_HtmlFormElement
{
    protected $template;

    /**
     * Init
     *
     * @return    void
     * @access    public
     */
    function init()
    {
        // define the custom attributes
        $this->defineAttribute('cssClass',      false,     'entities big ico-box span11',        COMPONENT_TYPE_STRING);
        $this->defineAttribute('cssClassLabel', false,     __Config::get('glizy.formElement.cssClassLabel'),        COMPONENT_TYPE_STRING);
        $this->defineAttribute('label',         false,     NULL,    COMPONENT_TYPE_STRING);
        $this->defineAttribute('value',         false,     NULL,    COMPONENT_TYPE_STRING);

        // call the superclass for validate the attributes
        parent::init();
    }

    function process()
    {
        $templateProxy = org_glizy_ObjectFactory::createObject('org.glizycms.template.models.proxy.TemplateProxy');
        $this->templates = $templateProxy->getAvailableTemplates();
        $this->_content = $templateProxy->getSelectedTemplate();
    }

    function render()
    {
// TODO: controllo acl
        $name = $this->getId();

        $attributes              = array();
        $attributes['id']        = $this->getId();
        $attributes['name']      = $this->getOriginalId();
        $attributes['disabled']  = $this->getAttribute('disabled') ? 'disabled' : '';
        $attributes['data-type'] = $this->getAttribute('data-type') ? $this->getAttribute('data-type') : 'selectpagetype';
        $attributes['class']     = $this->getAttribute('required') ? 'required' : '';
        $attributes['class']    .= $this->getAttribute( 'cssClass' ) != '' ? ( $attributes['class'] != '' ? ' ' : '' ).$this->getAttribute( 'cssClass' ) : '';
        $attributes['class']    .= ' hidden';
        $attributes['value']    = $this->_content;

        $output = '<input '.$this->_renderAttributes($attributes).'/>';
        $output .= '<ul class="'.$this->getAttribute( 'cssClass' ).'">';

        foreach ($this->templates as $template) {
            $output .= '<li>';
            $output .= '<a class="" data-type="'.$template->path.'"><img src="'.$template->preview.'" />'.$template->name.'</a>';
            $output .= '</li>';
        }

        $output .= '</ul>';
        $cssClassLabel = $this->getAttribute( 'cssClassLabel' );
        $cssClassLabel .= ( $cssClassLabel ? ' ' : '' ).($this->getAttribute('required') ? 'required' : '');
        $label = org_glizy_helpers_Html::label($this->getAttributeString('label'), $this->getId(), false, '', array('class' => $cssClassLabel ), false);

        $this->addOutputCode($this->applyItemTemplate($label, $output));
    }
}