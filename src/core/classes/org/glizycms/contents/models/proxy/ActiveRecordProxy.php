<?php
class org_glizycms_contents_models_proxy_ActiveRecordProxy extends GlizyObject
{
    public function load($recordId, $model)
    {
        $ar = org_glizy_objectFactory::createModel($model);

        if (__Config::get('ACL_MODULES')) {
            // permessi editing e visualizzazione record
            $this->addAclRelations($ar);
        }

        $ar->load($recordId);
        $values = $ar->getValuesAsArray();

        if (__Config::get('ACL_MODULES')) {
            $values['__aclEdit'] = $this->getPermissionName($ar->__aclEdit);
            $values['__aclView'] = $this->getPermissionName($ar->__aclView);
        }

        return $values;
    }

    protected function addAclRelations($ar) {
        $ar->addRelation(array('type' => 'joinTable', 'name' => 'rel_aclEdit', 'className' => 'org.glizy.models.JoinDoctrine', 'field' => 'join_FK_source_id', 'destinationField' => 'join_FK_dest_id',  'bindTo' => '__aclEdit', 'objectName' => ''));
        $ar->addRelation(array('type' => 'joinTable', 'name' => 'rel_aclView', 'className' => 'org.glizy.models.JoinDoctrine', 'field' => 'join_FK_source_id', 'destinationField' => 'join_FK_dest_id',  'bindTo' => '__aclView', 'objectName' => ''));
        $ar->setProcessRelations(true);
    }

    protected function getPermissionName($permissions)
    {
    	$names = array();
		$permissions = explode(',', $permissions);
		$ar = org_glizy_ObjectFactory::createModel('org.glizycms.roleManager.models.Role');
		foreach ($permissions as $v) {
			if ($ar->load($v)) {
				$names[] = array (
                    'id' => $ar->role_id,
                    'text' => $ar->role_name
                );
			}
		}

		return $names;
	}

    public function save($data)
    {
        $id = $data->__id;
        $model = $data->__model;

        $ar = org_glizy_objectFactory::createModel($model);
        $ar->load($id);

        foreach ($data as $k => $v) {
            // remove the system values
            if (strpos($k, '__') === 0 || !$ar->fieldExists($k)) continue;
            $ar->$k = $v;
        }

        try {
            if (__Config::get('ACL_MODULES')) {
                // permessi editing e visualizzazione record
                $this->addAclRelations($ar);
                $ar->__aclEdit = $data->__aclEdit;
                $ar->__aclView = $data->__aclView;
            }

            $id = $ar->save();
        }
        catch (org_glizy_validators_ValidationException $e) {
            return $e->getErrors();
        }

        return array('__id' => $id);
    }

    public function delete($recordId, $model)
    {
        $ar = org_glizy_objectFactory::createModel($model);

        if (__Config::get('ACL_MODULES')) {
            // permessi editing e visualizzazione record
            $this->addAclRelations($ar);
        }

        $ar->delete($recordId);
    }
}