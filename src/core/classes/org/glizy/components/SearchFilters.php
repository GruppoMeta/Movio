<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_components_SearchFilters extends org_glizy_components_Form
{
    protected $_filters = array();
    /** @var org_glizy_SessionEx $sessionEx  */
    protected $sessionEx = NULL;

    function init()
    {
        $this->defineAttribute('cssClass',  false, __Config::get('glizy.searchFilters.cssClass'),        COMPONENT_TYPE_STRING);
        $this->defineAttribute('wrapDiv',     false, false,     COMPONENT_TYPE_BOOLEAN);
        $this->defineAttribute('fieldset',     false, false,     COMPONENT_TYPE_BOOLEAN);
        $this->defineAttribute('rememberValues',     false, true,     COMPONENT_TYPE_BOOLEAN);
        $this->defineAttribute('rememberMode',     false, 'persistent',     COMPONENT_TYPE_STRING);
        $this->defineAttribute('setRequest',   false, false,     COMPONENT_TYPE_BOOLEAN);
        $this->defineAttribute('filterClass',   false, '',     COMPONENT_TYPE_STRING);

        parent::init();
        $this->setAttribute('addValidationJs', false);
    }

    function process()
    {
        $this->sessionEx     = new org_glizy_SessionEx($this->getId());
        $this->_command        = org_glizy_Request::get($this->getId().'_command');
        $this->rememberMode = $this->getAttribute( 'rememberMode' ) == 'persistent' ? GLZ_SESSION_EX_PERSISTENT : GLZ_SESSION_EX_VOLATILE;
        if ($this->_command=='RESET') $this->sessionEx->removeAll();
        $this->processChilds();
    }

    function &getDataProvider()
    {
        return $this->_dataProvider;
    }

    function loadContent($name, $bindToField=NULL, $value=NULL)
    {
        if (empty($bindToField))
        {
            $bindToField = $name;
        }

        if ($this->_command=='RESET')
        {
            $this->_filters[$bindToField] = '';
        }
        else
        {
            if ( $this->getAttribute( 'rememberValues') )
            {
                $defValue = !is_null( $this->sessionEx ) ? $this->sessionEx->get($name, '') : '';
                $this->_filters[$bindToField] = org_glizy_Request::get($name, $defValue );
            }
            else
            {
                $this->_filters[$bindToField] = org_glizy_Request::get($name, '');
            }
        }

        if ( !is_null( $this->sessionEx ) )
        {
            $this->sessionEx->set($name, $this->_filters[$bindToField], $this->rememberMode);
        }

        if ( $this->getAttribute('setRequest') )
        {
            __Request::set( $name, $this->_filters[$bindToField] );
        }

        return $this->_filters[$bindToField];
    }

    function setFilterValue($name, $value, $originalValue=null)
    {
        $this->_filters[$name] = $value;
        if ( !is_null( $this->sessionEx ) )
        {
            $this->sessionEx->set($name, is_null( $originalValue ) ?  $value : $originalValue, $this->rememberMode );
        }
    }

    function getFilters()
    {
        $filterClassName = $this->getAttribute('filterClass');
        $filterClass = $filterClassName ? org_glizy_ObjectFactory::createObject($filterClassName) : null;
        if (!$filterClass) {
            $tempFilters = $this->_filters;
            foreach($this->_filters as $k=>$v)
            {
                if (strpos($k, ',')!==false )
                {
                    unset($tempFilters[$k]);

                    if (!empty( $v )) {
                        $fields = explode(',', $k);
                        $tempOR = array();

                        foreach ($fields as $field) {
                            $tempOR[$field] = $v;
                        }
                        $tempFilters['__OR__'] = $tempOR;
                    }
                }
            }
        } else {
            $tempFilters = $filterClass->getFilters($this->_filters);
        }

        return $tempFilters;
    }

    function render_html_onStart()
    {
        if ($this->getAttribute('wrapDiv'))
        {
            $this->addOutputCode('<div'.$this->_renderAttributes(array('class' => $this->getAttribute('cssClass'))).'>');
            $this->setAttribute('cssClass', '');
        }
        parent::render_html_onStart();
        if ($this->getAttribute('fieldset')) $this->addOutputCode('<fieldset>');
    }

    function render_html_onEnd()
    {
        if ($this->getAttribute('fieldset'))
        {
            $this->addOutputCode('</fieldset>');
        }
        parent::render_html_onEnd();
        if ($this->getAttribute('wrapDiv'))
        {
            $this->addOutputCode('</div>');
        }
    }
}