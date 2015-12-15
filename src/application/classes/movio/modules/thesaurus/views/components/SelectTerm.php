<?php
class movio_modules_thesaurus_views_components_SelectTerm extends org_glizy_components_List
{
    protected $dictionaryId;

    function init()
    {
        // define the custom attributes
        $this->defineAttribute('value',         false,  '',    COMPONENT_TYPE_STRING);
        $this->defineAttribute('emptyValue', false,  0,    COMPONENT_TYPE_INTEGER);
       // $this->defineAttribute('emptyValueKey', false,  0,    COMPONENT_TYPE_INTEGER);

        // call the superclass for validate the attributes
        parent::init();
    }


    function process()
    {
        $this->dictionaryId = __Request::get('dictionaryId');

        $this->_content = $this->_parent->loadContent($this->getId());
        if (!$this->_content) {
            $this->_content = $this->getAttribute('value');
        }

        $this->_items = array();
        if ($this->_application->isAdmin()) {
            if (!is_null($this->getAttribute('emptyValue'))) {
                $this->addItem( $this->dictionaryId, html_entity_decode( $this->getAttributeString('emptyValue') ) );
            }

            $this->getItems();
        }
    }


    function getItems()
    {
        $thesaurusProxy = org_glizy_ObjectFactory::createObject('movio.modules.thesaurus.models.proxy.ThesaurusProxy');
        $this->getChildren($thesaurusProxy, $this->dictionaryId);
    }

    function getChildren($thesaurusProxy, $parentId, $level=0)
    {
        $childrens = $thesaurusProxy->getFirstLevelChildrens($this->dictionaryId, $parentId);
        foreach ($childrens as $child) {
            $pad = str_repeat('.  ', $level);
            $this->_items[] = array('key' => $child->getId(), 'value' => $pad.$child->term, 'selected' => $child->getId() == $this->_content ? 1 : 0);
            $this->getChildren($thesaurusProxy, $child->getId(), $level+1);
        }
    }
}