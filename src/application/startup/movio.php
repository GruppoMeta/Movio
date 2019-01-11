<?php
glz_loadLocale('movio');
glz_loadLocale('userModules.movio.ontologybuilder');

$application = org_glizy_ObjectValues::get('org.glizy', 'application' );
if ($application) {
    org_glizycms_speakingUrl_Manager::registerResolver(org_glizy_ObjectFactory::createObject('movio.modules.ontologybuilder.EntityResolver'));
    org_glizycms_speakingUrl_Manager::registerResolver(org_glizy_ObjectFactory::createObject('movio.modules.news.UrlResolver'));
    org_glizycms_speakingUrl_Manager::registerResolver(org_glizy_ObjectFactory::createObject('movio.modules.touristoperators.UrlResolver'));
}

movio_modules_news_Module::registerModule();
movio_modules_thesaurus_Module::registerModule();
movio_modules_touristoperators_Module::registerModule();

org_glizy_ObjectFactory::remapPageType('GoogleMap', 'StreetMap');
