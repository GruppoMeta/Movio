<?php
class org_glizycms_contents_controllers_pageEdit_Permissions extends org_glizy_mvc_core_Command
{
    public function execute($menuId)
    {
        if ($menuId) {
            $menu = org_glizy_ObjectFactory::createModel('org.glizycms.contents.models.Menu');
            $menu->load($menuId);

            //inserire menu_extendsPermissions nella tabella menus_tbl
            $data = new StdClass;
            $data->extendsPermissions = $menu->menu_extendsPermissions;

            $tableName = $menu->getTableName();

            $aclBack = array();

            $it = org_glizy_ObjectFactory::createModelIterator('org.glizycms.contents.models.Role')
                ->load('getAclBack', array('menuId' => $menuId, 'tableName' => $tableName));

            foreach ($it as $ar) {
                $aclBack[] = array(
                    'id' => $ar->role_id,
                    'text' => $ar->role_name
                );
            }

            $aclFront = array();

            $it = org_glizy_ObjectFactory::createModelIterator('org.glizycms.contents.models.Role')
                ->load('getAclFront', array('menuId' => $menuId, 'tableName' => $tableName));

            foreach ($it as $ar) {
                $aclFront[] = array(
                    'id' => $ar->role_id,
                    'text' => $ar->role_name
                );
            }

            $data->aclBack = $aclBack;
            $data->aclFront = $aclFront;
            $this->view->setData($data);
        }
    }
}