<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_components_Repeater extends org_glizy_components_ComponentContainer
{
    protected $repeaterId;
    protected $repeaterIdLen;
    protected $numRecords;
    protected $contentCount;


    /**
     * Init
     *
     * @return    void
     * @access    public
     */
    function init()
    {
        // define the custom attributes
        $this->defineAttribute('label',        false,     NULL,    COMPONENT_TYPE_STRING);
        // $this->defineAttribute('start',     false, NULL,     COMPONENT_TYPE_INTEGER);
        // $this->defineAttribute('count',     false, NULL,     COMPONENT_TYPE_INTEGER);
        $this->defineAttribute('newMode',    false,    false,    COMPONENT_TYPE_BOOLEAN);
        // $this->defineAttribute('routeUrl',         false,    NULL,    COMPONENT_TYPE_STRING);

        $this->defineAttribute('adm:min',     false, NULL,     COMPONENT_TYPE_INTEGER);
        $this->defineAttribute('adm:max',     false, NULL,     COMPONENT_TYPE_INTEGER);
        $this->defineAttribute('adm:collapsable', false, NULL,     COMPONENT_TYPE_INTEGER);

        // call the superclass for validate the attributes
        parent::init();
    }


    function process()
    {
        $this->repeaterId = $this->getId();
        $this->repeaterIdLen = strlen($this->repeaterId);
        $this->_content = $this->_parent->loadContent($this->repeaterId);
        $child = $this->childComponents[0];
        $childId = $child->getOriginalId();
        $this->numRecords = count($this->_content->$childId);
    }


    function getContent()
    {
        $result = array();
        for ($i = 0; $i < $this->numRecords; $i++) {
            $this->contentCount = $i;

            $temp = new StdClass;
            for ($j = 0; $j < count($this->childComponents); $j++) {
                $child = $this->childComponents[$j];
                $child->setContent(null);
                $child->process();
                $c = $child->getContent();
                $temp->{$child->getOriginalId()} = $c;
             }
             $result[] = $temp;
        }
        if ($this->getAttribute('newMode')) {
            $tempResult = new StdClass;
            $tempResult->cssClass = $this->getAttribute('cssClass');
            $tempResult->records = $result;
            return $tempResult;
        }
        return $result;
    }

    function loadContent($id, $bindTo='')
    {
        $id = substr($id, $this->repeaterIdLen + 1);
        return $this->_content->{$id}[$this->contentCount];
    }

    public static function compileAddPrefix($compiler, &$node, $componentId, $idPrefix)
    {
        return $idPrefix.'\''.$componentId.'-\'.';
    }

    public static function translateForMode_edit($node) {
        $min = $node->hasAttribute('adm:min') ? $node->getAttribute('adm:min') : '0';
        $max = $node->hasAttribute('adm:max') ? $node->getAttribute('adm:max') : '100';
        $collapsable = $node->hasAttribute('adm:collapsable') && $node->getAttribute('adm:collapsable') == 'true' ? 'true' : 'false';

        $attributes = array();
        $attributes['id'] = $node->getAttribute('id');
        $attributes['label'] = $node->getAttribute('label');
        $attributes['data'] = 'type=repeat;repeatMin='.$min.';repeatMax='.$max.';collapsable='.$collapsable;
        if ($node->hasAttribute('adm:data')) {
            $attributes['data'] .= ';'.$node->getAttribute('adm:data');
        }

        return org_glizy_helpers_Html::renderTag('glz:Fieldset', $attributes);
    }
}
