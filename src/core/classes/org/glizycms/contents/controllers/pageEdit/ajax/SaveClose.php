<?php
class org_glizycms_contents_controllers_pageEdit_ajax_SaveClose extends org_glizycms_contents_controllers_pageEdit_ajax_Save
{
    public function execute($data)
    {
        $r = parent::execute($data);

        if ($r===true) {
            $menuProxy = org_glizy_ObjectFactory::createObject('org.glizycms.contents.models.proxy.MenuProxy');
            $arMenu = $menuProxy->getMenuFromId($this->menuId, org_glizy_ObjectValues::get('org.glizy', 'editingLanguageId'));
            if ($arMenu->menu_type == org_glizycms_core_models_enum_MenuEnum::BLOCK) {
                return array('evt' => 'glizycms.pageEdit', 'message' => array('menuId' => $arMenu->menu_parentId));
            }
        }

        return $r;
    }
}