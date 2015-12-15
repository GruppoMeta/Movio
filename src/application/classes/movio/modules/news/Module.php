<?php
class movio_modules_news_Module
{
    static function registerModule()
    {
        $moduleVO = org_glizy_Modules::getModuleVO();
        $moduleVO->id = 'movio_news';
        $moduleVO->name = __T('News');
        $moduleVO->description = '';
        $moduleVO->version = '1.0.0';
        $moduleVO->classPath = 'movio.modules.news';
        $moduleVO->pageType = 'movio.modules.news.views.FrontEnd';
        $moduleVO->author = 'META srl';
        $moduleVO->authorUrl = 'http://www.gruppometa.it';
        $moduleVO->pluginUrl = 'http://movio.beniculturali.it';
        $moduleVO->siteMapAdmin = '<glz:Page pageType="movio.modules.news.views.Admin" id="movio_news" value="{i18n:News}" icon="icon-circle-blank" />';
        $moduleVO->showInOntologyBuilder = true;

        org_glizy_Modules::addModule( $moduleVO );
    }
}
