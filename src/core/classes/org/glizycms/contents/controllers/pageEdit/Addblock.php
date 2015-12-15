<?php
class org_glizycms_contents_controllers_pageEdit_Addblock extends org_glizycms_contents_controllers_pageEdit_Add
{
    public function execute($menuId)
    {
        if ($menuId) {
            parent::execute($menuId);

            // serve per impostare i filtri sui pagetype
            $menuProxy = org_glizy_ObjectFactory::createObject('org.glizycms.contents.models.proxy.MenuProxy');
            $arMenu = $menuProxy->getMenuFromId($menuId, org_glizy_ObjectValues::get('org.glizy', 'editingLanguageId'));
            $this->setComponentsAttribute('pageParent', 'data', 'options='.$arMenu->menu_pageType);
        }
    }
}