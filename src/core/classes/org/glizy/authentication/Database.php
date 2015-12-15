<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizy_authentication_Database extends org_glizy_authentication_AbstractLogin implements org_glizy_authentication_AuthenticationDriver
{
	public function login($loginId, $psw, $remember=false)
    {
        $this->validateLogin($loginId, $psw);
        $this->resetSession();

        $it = org_glizy_ObjectFactory::createModelIterator('org.glizy.models.User')
            ->load('login', array('loginId' => $loginId, 'password' => $psw));

        if ($it->count()) {
            // login success
            $this->arUser = $it->current();

            if ($this->arUser->user_isActive==0) {
                throw org_glizy_authentication_AuthenticationException::userNotActive();
            }

            if (__Config::get('ACL_ROLES') && $this->onlyBackendUser) {
                $user = array(
                    'id' => $this->arUser->user_id,
                    'firstName' => $this->arUser->user_firstName,
                    'lastName' => $this->arUser->user_lastName,
                    'loginId' => $this->arUser->user_loginId,
                    'email' => $this->arUser->user_email,
                    'groupId' => $this->arUser->user_FK_usergroup_id,
                    'backEndAccess' => false,
                );

                $user = &org_glizy_ObjectFactory::createObject('org.glizy.application.User', $user);

                if (!$user->acl('Home', 'all')) {
                    org_glizy_Session::destroy();
                    throw org_glizy_authentication_AuthenticationException::AccessNotAllowed();
                }

                $backEndAccess = true;
            }
            else {
                if ($this->onlyBackendUser && $this->arUser->usergroup_backEndAccess==0) {
                    throw org_glizy_authentication_AuthenticationException::AccessNotAllowed();
                }

                if (count($this->allowGroups) ? !in_array($this->arUser->user_FK_usergroup_id, $this->allowGroups) : false) {
                    throw org_glizy_authentication_AuthenticationException::AccessNotAllowed();
                }

                $backEndAccess = $this->arUser->usergroup_backEndAccess;
            }

            $language = $this->language;
            if (!$language) $language = __Config::get('DEFAULT_LANGUAGE');

            $user = array(  'id' => $this->arUser->user_id,
                            'firstName' => $this->arUser->user_firstName,
                            'lastName' => $this->arUser->user_lastName,
                            'loginId' => $this->arUser->user_loginId,
                            'email' => $this->arUser->user_email,
                            'groupId' => $this->arUser->user_FK_usergroup_id,
                            'backEndAccess' => $backEndAccess,
                            'language' => $language,
                            // 'logId' => $logId
                            );
            $this->setSession($user);

            if ($remember) {
                $this->setCookie($loginId, $psw);
            }

            $evt = array('type' => GLZ_EVT_USERLOGIN, 'data' => $user);
            $this->dispatchEvent($evt);
            return $user;
        } else {
            // wrong username or password
            throw org_glizy_authentication_AuthenticationException::wrongLoginIdOrPassword();
        }
    }

    public function logout()
    {
        org_glizy_Session::start();
        $evt = array('type' => GLZ_EVT_USERLOGOUT, 'data' => '');
        $this->dispatchEvent($evt);

        if (org_glizy_Config::get('USER_LOG')) {
            $user = org_glizy_Session::get('glizy.user');
            $arLog = &org_glizy_ObjectFactory::createModel('org.glizy.models.UserLog');
            $arLog->load($user['logId']);
            $arLog->delete();
        }
        org_glizy_Session::removeAll();

        setcookie( "glizy_username", "", time()-3600 );
        setcookie( "glizy_password", "", time()-3600 );
    }
}