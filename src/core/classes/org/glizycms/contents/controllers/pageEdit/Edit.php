<?php
class org_glizycms_contents_controllers_pageEdit_Edit extends org_glizy_mvc_core_Command
{
    public function execute($menuId)
    {
        $this->checkPermissionForBackend();
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

                $breadCrumbs = array($arMenu->menudetail_title);
                while ($arMenu->menu_type == org_glizycms_core_models_enum_MenuEnum::BLOCK) {
                    $arMenu = $menuProxy->getMenuFromId($arMenu->menu_parentId, org_glizy_ObjectValues::get('org.glizy', 'editingLanguageId'));
                    $breadCrumbs[] = '<a href="#" data-id="'.$arMenu->menu_id.'" class="js-glizycms-menu-edit">'.$arMenu->menudetail_title.'</a>';
                }

                $this->view->resetPageTitleModifier();
                $this->view->addPageTitleModifier(new org_glizycms_views_components_FormEditPageTitleModifierVO(
                                'edit',
                                __T('Edit page', implode(' > ', array_reverse($breadCrumbs))),
                                false,
                                '__id',
                                ''));
            }
        }
    }

    public function executeLater($menuId)
    {
        $statusToEdit = $this->view->statusToEdit();
        $availableStatus = $this->view->availableStatus();

        if (!$availableStatus) {
            $this->setComponentsVisibility(array('saveDraft', 'savePublish'), false);
        } else {
            $this->setComponentsVisibility(array('savePublish'), $statusToEdit==org_glizycms_contents_views_components_PageEdit::STATUS_DRAFT);
            $this->setComponentsVisibility(array('save'), $statusToEdit==org_glizycms_contents_views_components_PageEdit::STATUS_PUBLISHED);
        }
    }

}