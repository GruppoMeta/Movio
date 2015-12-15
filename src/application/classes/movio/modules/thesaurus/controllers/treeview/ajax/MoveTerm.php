<?php
class movio_modules_thesaurus_controllers_treeview_ajax_MoveTerm extends org_glizy_mvc_core_CommandAjax
{
    function execute($termId, $parentId)
    {
        $thesaurusProxy = org_glizy_ObjectFactory::createObject('movio.modules.thesaurus.models.proxy.ThesaurusProxy');
        $thesaurusProxy->moveTerm($termId, $parentId);

        return true;
    }
}