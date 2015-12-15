<?php
class org_glizycms_contents_controllers_pageEdit_ajax_SavePermissions extends org_glizy_mvc_core_CommandAjax
{
    public function execute($data)
    {
// TODO: controllo acl
        $data = json_decode($data);

        $aclBack = implode(',', $data->aclBack);
        $aclFront = implode(',', $data->aclFront);

        $ar = org_glizy_ObjectFactory::createModel('org.glizycms.contents.models.Menu');
        $ar->load($data->menuId);
        //$ar->aclBack = $data->editPermissions;
        //$ar->aclFront = $data->viewPermissions;
        $ar->menu_extendsPermissions = $data->extendsPermissions;
        $ar->menu_isLocked = $aclFront ? 1 : 0;
        $ar->save();

        $tableName = $ar->getTableName();

        $ar = org_glizy_ObjectFactory::createModel('org.glizy.models.JoinDoctrine');
        $ar->delete(array('join_objectName' => $tableName.'#rel_aclBack'));
        $ar->delete(array('join_objectName' => $tableName.'#rel_aclFront'));

        if ($aclBack != '') {
            $aclBack = explode(',', $aclBack);

            foreach ($aclBack as $role) {
                $ar->join_FK_source_id = $data->menuId;
                $ar->join_FK_dest_id = $role;
                $ar->join_objectName = $tableName.'#rel_aclBack';
                $ar->save(null, true);
            }
        }

        if ($aclFront != '') {
            $aclFront = explode(',', $aclFront);

            foreach ($aclFront as $role) {
                $ar->join_FK_source_id = $data->menuId;
                $ar->join_FK_dest_id = $role;
                $ar->join_objectName = $tableName.'#rel_aclFront';
                $ar->save(null, true);
            }
        }

        return true;
    }
}