<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class movio_views_components_RepeaterFromModel extends org_glizy_components_ComponentContainer
{
    private $repeaterId;
    private $numRecords;
    // private $contentCount;

    private $it;

    /**
     * Return the component information
     *
     * @return  array   component informaton.
     * @access  public
     * @static
     */
    function getInfo()
    {
        $info                   = parent::info();
        $info['name']           = 'Repeater';
        $info['class']          = 'org.glizy.components.Repeater';
        $info['package']        = 'Glizy';
        $info['version']        = GLZ_CORE_VERSION;
        $info['author']         = 'Daniele Ugoletti';
        $info['author-email']   = 'daniele.ugoletti@glizy.com';
        $info['url']            = 'http://www.glizy.com';
        return $info;
    }

        /**
     * Init
     *
     * @return  void
     * @access  public
     */
    function init()
    {
        // define the custom attributes
        $this->defineAttribute('label',     false,  NULL,   COMPONENT_TYPE_STRING);
        $this->defineAttribute('queryOr',     false,  false,   COMPONENT_TYPE_BOOLEAN);

        // call the superclass for validate the attributes
        parent::init();
    }


    function process()
    {
        $this->repeaterId = $this->getId();
        $this->it = org_glizy_ObjectFactory::createModelIterator('org.glizycms.models.Media');
        $this->numRecords  = 0;

        $filter = $this->_parent->loadContent($this->repeaterId);
        if (is_array($filter) && count($filter)) {
			$this->it->setFilters(array('media_type' => 'IMAGE'));
			
			$filters = array('media_category' => array());
            foreach($filter as $v) {
                $filters['media_category'][] = array('field' => 'media_category', 'value' => '%"'.$v.'"%', 'condition' => 'LIKE');
            }
            
            if ($this->getAttribute('queryOr')) {
                $this->it->setOrFilters($filters);
            } else {
                $this->it->setFilters($filters);
            }
            $this->it->orderBy('media_title');
            $this->numRecords = $this->it->count();
        }

        $this->_content = new StdClass;
    }


    function getContent()
    {
        $result = array();
        for ($i = 0; $i < $this->numRecords; $i++) {
            $this->contentCount = $i;
            $temp = new StdClass;
            for ($j = 0; $j < count($this->childComponents); $j++) {
                $child = $this->childComponents[$j];
                $child->process();
                $temp->{$child->getOriginalId()} = $child->getContent();
                $result[] = $temp;
            }
        }
        return $result;
    }

    function loadContent($id, $bindTo='')
    {
        $id = $this->it->current()->media_id;
        $this->it->next();
        return $id;
    }

    public static function compileAddPrefix($compiler, &$node, $componentId, $idPrefix)
    {
        return $idPrefix.'\''.$componentId.'-\'.';
    }

    public static function translateForMode_edit($node) {
        $attributes = array();
        $attributes['id'] = $node->getAttribute('id');
        $attributes['label'] = $node->getAttribute('label');
        $attributes['noChild'] = 'true';
        $attributes['data'] = '';
        if ($node->hasAttribute('adm:data')) {
            $attributes['data'] .= ';'.$node->getAttribute('adm:data');
        }

        return org_glizy_helpers_Html::renderTag('glz:Input', $attributes);
    }
}
