<?php
class movio_modules_thesaurus_Module
{
    static function registerModule()
    {
        glz_loadLocale('movio.modules.thesaurus');

        $moduleVO = org_glizy_Modules::getModuleVO();
        $moduleVO->id = 'movio_thesaurus';
        $moduleVO->name = __T('movio_thesaurus');
        $moduleVO->description = '';
        $moduleVO->version = '1.0.0';
        $moduleVO->classPath = 'movio.modules.thesaurus';
        $moduleVO->author = 'META srl';
        $moduleVO->authorUrl = 'http://www.gruppometa.it';
        $moduleVO->pluginUrl = 'http://movio.beniculturali.it/';
        $moduleVO->siteMapAdmin = '<glz:Page pageType="movio.modules.thesaurus.views.Admin" id="movio_thesaurus" value="{i18n:'.$moduleVO->name.'}" icon="icon-circle-blank" adm:acl="*" />';

        org_glizy_Modules::addModule( $moduleVO );
    }
}
