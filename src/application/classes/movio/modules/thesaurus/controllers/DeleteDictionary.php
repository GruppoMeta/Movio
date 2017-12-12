<?php
class movio_modules_thesaurus_controllers_DeleteDictionary extends org_glizy_mvc_core_Command
{
    public function execute($dictionaryId)
    {
        $this->checkPermissionForBackend();
        
        $thesaurusProxy = org_glizy_ObjectFactory::createObject('movio.modules.thesaurus.models.proxy.ThesaurusProxy');
        $thesaurusProxy->deleteDictionary($dictionaryId);
        
        $this->changeAction('index');
    }
}