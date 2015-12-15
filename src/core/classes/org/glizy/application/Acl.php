<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizy_application_Acl extends GlizyObject
{
    protected $id;
    protected $groupId;
    protected $_acl;
    protected $application;

    function __construct( $id, $groupId)
    {
        $this->id = $id;
        $this->groupId = $groupId;

        $this->application = org_glizy_ObjectValues::get('org.glizy', 'application');
        $fileName = org_glizy_Paths::getRealPath('APPLICATION', 'config/acl.xml');
        $compiler = org_glizy_ObjectFactory::createObject('org.glizy.compilers.Acl');
        $compiledFileName = $compiler->verify($fileName);

        include($compiledFileName);
        $this->_acl = $acl;
    }


    function acl($name, $action, $default=NULL)
    {
        $action = strtolower($action);
        $name   = $name=='*' ? strtolower($this->application->getPageId()) : strtolower($name);
        if (isset($this->_acl[$name]))
        {
            if (isset($this->_acl[$name]['rules'][$action]))
            {
                $rules = $this->_acl[$name]['rules'][$action];
                return in_array($this->groupId, $rules['allowGroups']) || in_array('*', $rules['allowGroups']) ?
                                        true
                                        :
                                        in_array($this->id, $rules['allowUsers']) ? true : false;
            }
            else
            {
                return is_null($default) ? $this->_acl[$name]['default'] : $default;
            }
        }
        else
        {
            return is_null($default) ? true : $default;
        }
    }

    function checkActions($action, $role)
    {
        for ($i=0; $i<count($role); $i++)
        {
            $action = $action==$role[$i][1] ? ($this->acl($role[$i][0], $role[$i][1]) ? $role[$i][2] : $role[$i][3]) : $action;
        }
        return $action;
    }

    function invalidateAcl()
    {
        $fileName = org_glizy_Paths::getRealPath('APPLICATION', 'config/acl.xml');
        $compiler = org_glizy_ObjectFactory::createObject('org.glizy.compilers.Acl');
        $compiler->invalidate($fileName);
    }
}