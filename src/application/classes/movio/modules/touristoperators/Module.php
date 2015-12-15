<?php
class movio_modules_touristoperators_Module
{
    static function registerModule()
    {
        $moduleVO = org_glizy_Modules::getModuleVO();
        $moduleVO->id = 'movio_touristoperators';
        $moduleVO->name = __T('Tourist Operators');
        $moduleVO->description = '';
        $moduleVO->version = '1.0.0';
        $moduleVO->classPath = 'movio.modules.touristoperators';
        $moduleVO->pageType = 'movio.modules.touristoperators.views.FrontEnd';
        $moduleVO->author = 'META srl';
        $moduleVO->authorUrl = 'http://www.gruppometa.it';
        $moduleVO->pluginUrl = 'http://movio.beniculturali.it';
        $moduleVO->siteMapAdmin = '<glz:Page pageType="movio.modules.touristoperators.views.Admin" id="movio_touristoperators" value="{i18n:Tourist Operators}" icon="icon-circle-blank" />';
        $moduleVO->showInOntologyBuilder = true;
        
        org_glizy_Modules::addModule( $moduleVO );
    }
}
