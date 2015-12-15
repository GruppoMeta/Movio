<?php 

class org_glizycms_roleManager_services_RoleService extends GlizyObject
{
	function addModule($moduleId, $permission = array('visible' => 'true'))
	{
        $it = org_glizy_ObjectFactory::createModelIterator('org.glizycms.roleManager.models.Role');
        
        foreach ($it as $ar) {
            $permissions = unserialize($ar->role_permissions);
            $permissions[$moduleId] = $permission;
            $ar->role_permissions = serialize($permissions);
            $ar->save();
        }
	}
}