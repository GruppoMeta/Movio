<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizy_application_AclAdvanced extends org_glizy_application_Acl
{
    protected $roles;
    protected $aclMatrix;

    function __construct($id, $groupId)
    {
        parent::__construct($id, $groupId);

        $this->roles = array();
        $this->aclMatrix = array();

        if ($id)  {
            // TODO ora la matrice è memorizzata nella sessione
            // e non può essere invalidata dal gestore dei ruoli per tutti gli utenti
            $roles = org_glizy_Session::exists('glizy.roles');
            if (!empty($roles)) {
                $this->roles = org_glizy_Session::get('glizy.roles');
                $this->aclMatrix = org_glizy_Session::get('glizy.aclMatrix');
            } else {
                $it = org_glizy_ObjectFactory::createModelIterator('org.glizy.models.Role', 'getPermissions', array('params' => array('id' => $id, 'groupId' => $groupId)));

                foreach ($it as $ar) {
                    // se il ruolo non è attivo passa al prossimo
                    if (!$ar->role_active) continue;

                    // se il ruolo non è stato ancora processato
                    if (!$this->roles[$ar->role_id]) {
                        $this->roles[$ar->role_id] = true;
                        $permissions = unserialize($ar->role_permissions);
                        // unione delle matrici dei permessi
                        foreach ($permissions as $name => $actions) {
                            foreach ((array)$actions as $action => $value) {
                                $this->aclMatrix[strtolower($name)][$action] |= $value;
                            }
                        }
                    }
                }

                org_glizy_Session::set('glizy.roles', $this->roles);
                org_glizy_Session::set('glizy.aclMatrix', $this->aclMatrix);
            }
        }
    }

    function acl($name, $action, $default=null)
    {
        $name = $name=='*' ? strtolower($this->application->getPageId()) : strtolower($name);
        return $this->aclMatrix[$name]['all'] || $this->aclMatrix[$name][$action];
    }

    function inRole($roleId)
    {
        return $this->roles[$roleId];
    }

    function getRoles()
    {
        return array_keys($this->roles);
    }

    function invalidateAcl()
    {
    }
}