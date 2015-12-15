<?php
class org_glizycms_contents_controllers_siteTree_ajax_AddPage extends org_glizy_mvc_core_CommandAjax
{
    public function execute($menuId, $title, $pageType) {
// TODO: CONTROLLO ACL
        if ($menuId) {
            $menuProxy = org_glizy_ObjectFactory::createObject('org.glizycms.contents.models.proxy.MenuProxy');
            $menuProxy->addMenu($title, $menuId, $pageType);
            return true;
        }

        return false;
    }
}