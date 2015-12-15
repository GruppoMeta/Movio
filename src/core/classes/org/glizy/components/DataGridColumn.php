<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_components_DataGridColumn extends org_glizy_components_Component
{
    /**
     * Init
     *
     * @return    void
     * @access    public
     */
    function init()
    {
        // define the custom attributes
        $this->defineAttribute('align',            false,     'left',    COMPONENT_TYPE_STRING);
        $this->defineAttribute('columnName',    false,     NULL,    COMPONENT_TYPE_STRING);
        $this->defineAttribute('cssClass',        false,     NULL,    COMPONENT_TYPE_STRING);
        $this->defineAttribute('command',        false,     '',        COMPONENT_TYPE_STRING);
        $this->defineAttribute('headerText',    false,     NULL,    COMPONENT_TYPE_STRING);
        $this->defineAttribute('key',            false,     false,    COMPONENT_TYPE_BOOLEAN);
        $this->defineAttribute('renderCell',    false,     '',        COMPONENT_TYPE_STRING);
        $this->defineAttribute('width',            false,     NULL,    COMPONENT_TYPE_INTEGER);
        $this->defineAttribute('orderable',        false, true,    COMPONENT_TYPE_BOOLEAN);
        $this->defineAttribute('aclService',       false,    'EditRecord', COMPONENT_TYPE_STRING);

        // call the superclass for validate the attributes
        parent::init();
    }

    function getProperties()
    {
        $properties = array();
        $properties['columnName'] = $this->getAttribute('columnName');
        $properties['headerText'] = $this->getAttribute('headerText');
        $properties['width']      = $this->getAttribute('width');
        $properties['align']      = $this->getAttribute('align');
        $properties['visible']    = $this->getAttribute('visible');
        $properties['key']        = $this->getAttribute('key');
        $properties['command']    = $this->getAttribute('command');
        $properties['renderCell'] = $this->getAttribute('renderCell');
        $properties['cssClass']   = $this->getAttribute('cssClass');
        $properties['orderable']  = $this->getAttribute('orderable');
        $properties['id']         = $this->getOriginalId();
        $properties['aclService'] = $this->getAttribute('aclService');

        return $properties;
    }
}