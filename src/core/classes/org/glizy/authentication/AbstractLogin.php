<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

abstract class org_glizy_authentication_AbstractLogin extends GlizyObject
{
    protected $loginId;
    protected $language = null;
    protected $psw;
    protected $arUser;
    protected $allowGroups = array();
    protected $onlyBackendUser = false;

    public function loginFromRequest($loginIdField, $passwordFields, $rememberField=false, $readFromCookie=false)
    {
        $loginId   = trim(__Request::get($loginIdField, $readFromCookie ? @$_COOKIE['glizy_username'] : '' ));
        $psw       = trim(__Request::get($passwordFields, $readFromCookie ? @$_COOKIE['glizy_password'] : ''));
        $remember  = __Request::get($rememberField, 0);
        $this->login($loginId, glz_password($psw), $remember);
    }

    public function setAllowGroups($allowGroups)
    {
        $this->allowGroups = $allowGroups;
    }

    public function setOnlyBackendUser($onlyBackendUser)
    {
        $this->onlyBackendUser = $onlyBackendUser;
    }

    public function setUserLanguage($language)
    {
        $this->language = $language;
    }

	protected function validateLogin($loginId, $psw)
    {
        if (!$loginId || !$psw) {
            throw org_glizy_authentication_AuthenticationException::emptyLoginIdOrPassword();
        }
    }

    protected function resetSession() {
        org_glizy_Session::set('glizy.userLogged', false);
        org_glizy_Session::set('glizy.user', NULL);
    }

    protected function setSession($user) {
        org_glizy_Session::set('glizy.userLogged', true);
        org_glizy_Session::set('glizy.user', $user);
    }

    protected function resetCookie() {
    }

    protected function setCookie($loginId, $psw) {
        $lifetime = time() + 60*60*24*30;
        setcookie( "glizy_username", $loginId, $lifetime); //, "/", $_SERVER["HTTP_HOST"], !$_SERVER["HTTPS"]);
        setcookie( "glizy_password", $psw, $lifetime); //, "/", $_SERVER["HTTP_HOST"], !$_SERVER["HTTPS"]);
    }

}
