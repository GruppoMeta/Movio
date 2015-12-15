<?php
class org_glizycms_contents_controllers_pageEdit_Edit extends org_glizy_mvc_core_Command
{
    public function execute($menuId)
    {
        // TODO controllo permessi
        if ($menuId) {
            // controlla se il menù è di tipo Block
            $menuProxy = org_glizy_ObjectFactory::createObject('org.glizycms.contents.models.proxy.MenuProxy');
            $arMenu = $menuProxy->getMenuFromId($menuId, org_glizy_ObjectValues::get('org.glizy', 'editingLanguageId'));

            $previewUrl = GLZ_HOST.'/../?pageId='.($arMenu->menu_type != org_glizycms_core_models_enum_MenuEnum::BLOCK ? $menuId : $arMenu->menu_parentId);

            $this->setComponentsAttribute('preview', 'visible', true);
            $this->setComponentsAttribute('preview', 'url', $previewUrl);
            $this->setComponentsVisibility('saveAndClose', $arMenu->menu_type == org_glizycms_core_models_enum_MenuEnum::BLOCK);

            if ($arMenu->menu_type == org_glizycms_core_models_enum_MenuEnum::BLOCK) {
                $this->view->setAttribute('editUrl', false);
                $this->setComponentsAttribute('propertiesState', 'draw', false);
                $this->setComponentsAttribute('templateState', 'draw', false);


            }
        }
    }
}