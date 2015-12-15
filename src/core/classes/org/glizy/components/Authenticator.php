<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_components_Authenticator extends org_glizy_components_Component
{
    /**
     * Init
     *
     * @return    void
     * @access    public
     */
    function init()
    {
        $this->defineAttribute('cssClass',        false,     '',        COMPONENT_TYPE_STRING);
        $this->defineAttribute('accessPageId',    true,     '',        COMPONENT_TYPE_STRING);
        $this->defineAttribute('logoutPageId',    false,     '',        COMPONENT_TYPE_STRING);
        $this->defineAttribute('label',            false,     'Logout',        COMPONENT_TYPE_STRING);
        $this->defineAttribute('allowGroups',    false,     '',        COMPONENT_TYPE_STRING);
        $this->defineAttribute('showErrorMessage',    false,     true, COMPONENT_TYPE_BOOLEAN);
        $this->defineAttribute('checkAcl',     false,     false, COMPONENT_TYPE_BOOLEAN);
        $this->defineAttribute('icon',            false,     'icon-signout',        COMPONENT_TYPE_STRING);

        parent::init();
    }


    function process()
    {
        $allowGroups = $this->getAttribute('allowGroups')!='' ? explode(',', $this->getAttribute('allowGroups')) : array();

        if ( ( !org_glizy_Session::get('glizy.userLogged') || !(count($allowGroups) ? in_array($this->_user->groupId, $allowGroups) : true)  )  && $this->getAttribute( 'enabled' ) )
        {
            if (org_glizy_helpers_Link::scriptUrl() != org_glizy_helpers_Link::makeUrl('link', array('pageId' => org_glizy_Config::get('START_PAGE'))))
            {
                if ( $this->getAttribute( 'showErrorMessage' ) )
                {
                    org_glizy_Session::set('glizy.loginError', org_glizy_locale_Locale::get('GLZ_LOGIN_NOACCESS'));
                }
                org_glizy_Session::set('glizy.loginUrl', __Request::get('__url__'));
            }
            org_glizy_helpers_Navigation::gotoUrl(org_glizy_helpers_Link::makeUrl('link', array('pageId' => $this->getAttribute('accessPageId'))));
            exit;
        }

        if ($this->getAttribute('checkAcl') && !$this->_user->acl($this->_application->getPageId(), 'visible')) {
            header('HTTP/1.0 403 Forbidden');
            exit();
        }
    }

    function render_html()
    {
        if ( $this->getAttribute( 'logoutPageId' ) )
        {
            $output = org_glizy_helpers_Link::makeLink('link', array(    'pageId' => $this->getAttribute('logoutPageId'),
                                                                        'title' => $this->getAttribute( 'label' ),
                                                                        'cssClass' => $this->getAttribute( 'cssClass' ),
                                                                        'icon' => $this->getAttribute( 'icon' )) );
            $this->addOutputCode($output);
        }
    }
}