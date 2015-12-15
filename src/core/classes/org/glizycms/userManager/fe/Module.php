<?php
class org_glizycms_userManager_fe_Module
{
    static function registerModule()
    {
        $moduleVO = org_glizy_Modules::getModuleVO();
        $moduleVO->id = 'glizycms.userManager.fe';
        $moduleVO->name = __T('User Manager');
        $moduleVO->description = '';
        $moduleVO->version = '1.0.0';
        $moduleVO->classPath = 'org.glizycms.userManager.fe';
        $moduleVO->author = 'META srl';
        $moduleVO->authorUrl = 'http://www.gruppometa.it';

        org_glizy_Modules::addModule( $moduleVO );
    }
}