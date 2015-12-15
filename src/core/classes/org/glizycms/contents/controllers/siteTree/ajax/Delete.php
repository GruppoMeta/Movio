<?php
class org_glizycms_contents_controllers_siteTree_ajax_Delete extends org_glizy_mvc_core_CommandAjax
{
    public function execute($menuId) {
// TODO: CONTROLLO ACL
        $menuProxy = org_glizy_ObjectFactory::createObject('org.glizycms.contents.models.proxy.MenuProxy');
        $menuProxy->deleteMenu($menuId);

        return true;
    }
}