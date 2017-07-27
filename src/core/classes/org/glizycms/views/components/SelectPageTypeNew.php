<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2011 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizycms_views_components_SelectPageTypeNew extends org_glizy_components_HtmlFormElement
{
    protected $items = array();
    /**
     * Init
     *
     * @return    void
     * @access    public
     */
    function init()
    {
        // define the custom attributes
        $this->defineAttribute('bindTo',        false,     NULL,    COMPONENT_TYPE_STRING);
        $this->defineAttribute('cssClass',      false,     '',        COMPONENT_TYPE_STRING);
        $this->defineAttribute('cssClassLabel', false,     __Config::get('glizy.formElement.cssClassLabel'),        COMPONENT_TYPE_STRING);
        $this->defineAttribute('label',         false,     NULL,    COMPONENT_TYPE_STRING);
        $this->defineAttribute('value',         false,     NULL,    COMPONENT_TYPE_STRING);
        $this->defineAttribute('showAllPageTypes', false,     __Config::get('glizycms.content.showAllPageTypes'),    COMPONENT_TYPE_BOOLEAN);
        $this->defineAttribute('linked', false,     '',    COMPONENT_TYPE_STRING);
        $this->defineAttribute('onlyWithParent', false,     '',    COMPONENT_TYPE_STRING);
        $this->defineAttribute('hide', false, false,    COMPONENT_TYPE_BOOLEAN);
        $this->defineAttribute('hideBlock', false, false,    COMPONENT_TYPE_BOOLEAN);

        // call the superclass for validate the attributes
        parent::init();
    }

    function process()
    {
        $this->_content = $this->getAttribute('value');
        if (is_object($this->_content))
        {
            // legge il contenuto da un dataProvider
            $contentSource = &$this->getAttribute('value');
            $this->_content = $contentSource->loadContent($this->getId(), $this->getAttribute('bindTo'));
        }
        else if (is_null($this->_content))
        {
            // richiede il contenuto al padre
            $this->_content = $this->_parent->loadContent($this->getId(), $this->getAttribute('bindTo'));
        }

        $this->readFromXml();
        $this->readFromFolder();
        $this->readFromModules();

        org_glizy_helpers_Array::arrayMultisortByLabel($this->items, 'label');
    }

    function render()
    {
        // TODO: controllo acl
        $name = $this->getId();

        if (!$this->_user->acl($this->_application->getPageId(),'new') || $this->getAttribute('hide'))
        {
            $output = org_glizy_helpers_Html::hidden( $name , $this->_content, array( 'class' => $this->getAttribute( 'cssClass' ) ) );
            $this->addOutputCode($output);
        }
        else
        {
            $attributes              = array();
            $attributes['id']        = $this->getId();
            $attributes['name']      = $this->getOriginalId();
            $attributes['disabled']  = $this->getAttribute('disabled') ? 'disabled' : '';
            $attributes['data-type'] = 'selectpagetype';
            $attributes['data-linked'] = $this->getAttribute('linked');
            $attributes['data-onlywithparent'] = $this->getAttribute('onlyWithParent');
            $attributes['class']     = $this->getAttribute('required') ? 'required' : '';
            $attributes['class']    .= $this->getAttribute( 'cssClass' ) != '' ? ( $attributes['class'] != '' ? ' ' : '' ).$this->getAttribute( 'cssClass' ) : '';
            $attributes['class']    .= ' hidden';

            $output = '<input '.$this->_renderAttributes($attributes).'/>';
            $output .= '<ul class="pageTypeSelect">';

            $hideBlock = $this->getAttribute('hideBlock');

            foreach ($this->items as $v) {
                if ($hideBlock && $v['isBlock']) continue;
                $output .= '<li>';
                $output .= '<a class="'.$v['cssClass'].'" data-type="'.$v['type'].'" data-acceptparent="'.@$v['acceptParent'].'">'.$v['label'].'</a>';
                $output .= '</li>';
            }
            $output .= '</ul>';

            $cssClassLabel = $this->getAttribute( 'cssClassLabel' );
            $cssClassLabel .= ( $cssClassLabel ? ' ' : '' ).($this->getAttribute('required') ? 'required' : '');

            if ($this->getAttribute('wrapLabel')) {
                $label = org_glizy_helpers_Html::label($this->getAttributeString('label'), $this->getId(), true, $output, array('class' => $cssClassLabel ), false);
                $output = '';
            } else {
                $label = org_glizy_helpers_Html::label($this->getAttributeString('label'), $this->getId(), false, '', array('class' => $cssClassLabel ), false);
            }

            $this->addOutputCode($this->applyItemTemplate($label, $output));
        }
    }


    protected function readFromXml($fileName = 'pageTypes.xml')
    {
        $pageTypeService = org_glizy_ObjectFactory::createObject('org.glizycms.contents.services.PageTypeService', $fileName);
        $pageTypes = $pageTypeService->getAllPageTypes();

        foreach ($pageTypes as $pageTypeName => $pageType) {
            if (!isset($this->items[$pageTypeName])) {
                if ($pageType['unique'] && $pageTypeName != $this->_content) {
                    // TODO migliorare
                    $ar = &org_glizy_ObjectFactory::createModel('org.glizycms.core.models.Menu');
                    $result = $ar->find(array('menu_pageType' => $pageTypeName));
                    unset($ar);
                    if ($result) {
                        continue;
                    }
                }

                if (preg_match("/\{i18n\:.*\}/i", $pageType['label'])) {
                    $code = preg_replace("/\{i18n\:(.*)\}/i", "$1", $pageType['label']);
                    $pageType['label'] = org_glizy_locale_Locale::getPlain($code);
                }

                $this->items[$pageTypeName] = array('label' => $pageType['label'],
                                                        'type' => $pageTypeName,
                                                        'cssClass' =>  $pageType['class'],
                                                        'acceptParent' =>  $pageType['acceptParent'],
                                                        'isBlock' =>  $pageType['isBlock'],
                                                        );
            }
        }
    }

    protected function readFromFolder()
    {
        if ($this->getAttribute('showAllPageTypes')) {
            glz_loadLocale(org_glizy_Paths::get('APPLICATION_TO_ADMIN_PAGETYPE'));
            foreach (glob(org_glizy_Paths::get('APPLICATION_TO_ADMIN_PAGETYPE').'*.xml') as $file) {
                $pathInfo = pathinfo($file);
                if ($pathInfo['basename']=='Common.xml' || isset($this->items[$pathInfo['filename']])) continue;
                $this->items[$pathInfo['filename']] = array('label' => __T($pathInfo['filename']),
                                                        'type' => $pathInfo['filename'],
                                                        'cssClass' =>  'button-generic',
                                                        'isBlock' => false);
            }
        }
    }

    protected function readFromModules()
    {
        if ($this->getAttribute('showAllPageTypes')) {
            $modules = org_glizy_Modules::getModules();
            foreach($modules as $moduleVO) {
                $pageType = $moduleVO->pageType;
                if ($pageType) {
                    if (isset($this->items[$pageType])) continue;
                    if (!$moduleVO->show) {
                        continue;
                    }

                    if ($moduleVO->unique && $pageType != $this->_content) {
                        // TODO migliorare
                        $ar = &org_glizy_ObjectFactory::createModel('org.glizycms.core.models.Menu');
                        $result = $ar->find(array('menu_pageType' => $pageType));
                        unset($ar);
                        if ($result) {
                            continue;
                        }
                    }

                    $this->items[$pageType] = array('label' => __T($pageType),
                                                            'type' => $pageType,
                                                            'cssClass' =>  'button-generic');
                }
            }
        }
    }
}
