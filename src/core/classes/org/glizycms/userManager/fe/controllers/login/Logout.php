<?php
class org_glizycms_userManager_fe_controllers_login_Logout extends org_glizy_mvc_core_Command
{
    public function execute()
    {
        $authClass = org_glizy_ObjectFactory::createObject(__Config::get('glizy.authentication'));
        $authClass->logout();

        org_glizy_helpers_Navigation::gotoUrl( GLZ_HOST );
        exit();
    }
}