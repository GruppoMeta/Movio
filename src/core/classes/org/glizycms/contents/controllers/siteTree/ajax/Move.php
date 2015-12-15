<?php
class org_glizycms_contents_controllers_siteTree_ajax_Move extends org_glizy_mvc_core_CommandAjax
{
    public function execute($menuId, $position, $parentId) {
// TODO: CONTROLLO ACL
        $menuProxy = org_glizy_ObjectFactory::createObject('org.glizycms.contents.models.proxy.MenuProxy');
        $menuProxy->moveMenu($menuId, $position, $parentId);

        return true;
    }
}