<?php
class movio_modules_thesaurus_views_components_SelectDictionary extends org_glizy_components_List
{
    function process()
    {
        $this->_content = $this->_parent->loadContent($this->getId());
        if (!$this->_content) {
            $this->_content = $this->getAttribute('value');
        }

        $this->_items = array();
        if ($this->_application->isAdmin()) {
            $this->addItem( '', '-' );
            $this->getItems();
        }
    }


    function getItems()
    {
        $thesaurusProxy = org_glizy_ObjectFactory::createObject('movio.modules.thesaurus.models.proxy.ThesaurusProxy');
        $dictionaries = $thesaurusProxy->getAllDictionaries();
        foreach ($dictionaries as $dictionary) {
            $this->addItem( $dictionary->getId(), $dictionary->title );
        }
    }
}