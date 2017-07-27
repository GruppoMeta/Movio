<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

/** class org_glizy_application_User */
class org_glizy_application_User extends GlizyObject
{

    var $id;
    var $firstName;
    var $lastName;
    var $loginId;
    var $email;
    var $groupId;
    var $backEndAccess;
    var $language;
    var $active;
    var $dateCreation;
    var $_acl = null;

    function __construct($userInfo)
    {
        if (is_object($userInfo)) {
            $userInfo = (array)$userInfo;
        }
        $this->id            = $userInfo['id'];
        $this->firstName     = $userInfo['firstName'];
        $this->lastName      = $userInfo['lastName'];
        $this->loginId       = $userInfo['loginId'];
        $this->email         = $userInfo['email'];
        $this->groupId       = $userInfo['groupId'];
        $this->backEndAccess = $userInfo['backEndAccess']==1;
        $this->language      = isset($userInfo['language']) ? $userInfo['language'] : '';
        $this->active        = isset($userInfo['isActive']) ? $userInfo['isActive'] : false;
        $this->dateCreation  = isset($userInfo['dateCreation']) ? $userInfo['dateCreation'] : '';

        // TODO gestire __Config::get('ACL_ENABLED')
        // creando un UserAcl che viene creato se __Config::get('ACL_ENABLED') = true
        $this->_acl = org_glizy_ObjectFactory::createObject(__Config::get('ACL_CLASS'), $this->id, $this->groupId);

        org_glizy_ObjectValues::set('org.glizy', 'userId', $this->id);
    }

    function getId()
    {
        return $this->id;
    }

    function toString()
    {
        return "user: " . $this->id . ", " . $this->loginId . ", " . $this->firstName . " " . $this->lastName;
    }

    function isLogged()
    {
        return $this->id <> 0;
    }

    function acl($name, $action, $default = null)
    {
        return $this->_acl->acl($name, $action, $default);
    }

    function checkActions($action, $role)
    {
        return $this->_acl->checkActions($action, $role);
    }

    function isInRole($roleId)
    {
        return $this->_acl->inRole($roleId);
    }

    function isInRoles($roles)
    {
        foreach ($roles as $roleId) {
            if ($this->_acl->inRole($roleId)) {
                return true;
            }
        }

        return false;
    }

    function getRoles()
    {
        return $this->_acl->getRoles();
    }

    function invalidateAcl()
    {
        return $this->_acl->invalidateAcl();
    }

    function isActive()
    {
        return $this->active;
    }

    /**
     * @return mixed
     */
    public function getDateCreation()
    {
        return $this->dateCreation;
    }
}