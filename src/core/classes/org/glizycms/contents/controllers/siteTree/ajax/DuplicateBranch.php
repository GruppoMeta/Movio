<?php
class org_glizycms_contents_controllers_siteTree_ajax_DuplicateBranch extends org_glizy_mvc_core_CommandAjax
{
    public function execute($menuId)
    {
        $this->checkPermissionForBackend();
        $contentProxy = org_glizy_ObjectFactory::createObject('org.glizycms.contents.models.proxy.ContentProxy');
        $contentProxy->duplicateMenuAndContent($menuId, true);

        return true;
    }
}