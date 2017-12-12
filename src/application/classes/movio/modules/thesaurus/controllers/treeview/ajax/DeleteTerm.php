<?php
class movio_modules_thesaurus_controllers_treeview_ajax_DeleteTerm extends org_glizy_mvc_core_CommandAjax
{
    function execute($termId)
    {
        $this->checkPermissionForBackend();
        $thesaurusProxy = org_glizy_ObjectFactory::createObject('movio.modules.thesaurus.models.proxy.ThesaurusProxy');
        $thesaurusProxy->deleteTerm($termId);

        return true;
    }
}