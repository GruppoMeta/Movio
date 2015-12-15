<?php
class movio_modules_ontologybuilder_controllers_ajax_GetDictionaries extends org_glizy_mvc_core_CommandAjax
{
    function execute()
    {
        $thesaurusProxy = org_glizy_ObjectFactory::createObject('movio.modules.thesaurus.models.proxy.ThesaurusProxy');
        $dictionaries = $thesaurusProxy->getAllDictionaries();
        
        $result = array();
        
        foreach ($dictionaries as $dictionary) {
            $result[] = array(
                'id' => $dictionary->getId(),
                'name' => $dictionary->title
            );
        }
        
        return $result;
    }
}