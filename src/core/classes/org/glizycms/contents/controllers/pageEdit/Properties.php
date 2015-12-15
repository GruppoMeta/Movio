<?php
class org_glizycms_contents_controllers_pageEdit_Properties extends org_glizy_mvc_core_Command
{
    public function execute($menuId)
    {
        if ($menuId) {
            $menu = org_glizy_ObjectFactory::createModel('org.glizycms.core.models.Menu');
            $menu->load($menuId);
            $data = $menu->getValuesAsArray();
            // TODO controllase se il componente deve essere nascosto
            // quando ci sono pagine che devono essere usate una sola volta
            $this->setComponentsAttribute('menu_pageType', 'hide', $menu->menu_type == 'SYSTEM');

            $menuDetail = org_glizy_ObjectFactory::createModel('org.glizycms.core.models.MenuDetail');
            $menuDetail->find(array('menudetail_FK_menu_id' => $menuId, 'menudetail_FK_language_id' => org_glizy_ObjectValues::get('org.glizy', 'editingLanguageId')));
            $data = array_merge($data, $menuDetail->getValuesAsArray());

            if ($menu->menu_parentId) {
                $menuParent = org_glizy_ObjectFactory::createModel('org.glizycms.core.models.Menu');
                $menuParent->load($menu->menu_parentId);
                $data['menu_parentPageType'] = $menuParent->menu_pageType;
            }

            if ($this->user->acl('glizycms', 'page.properties.modifyPageTypeFree')) {
                $this->setComponentsAttribute('menu_pageType', 'linked', '');
            }

            $this->view->setData($data);
        }

    }
}