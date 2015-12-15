<?php
class org_glizycms_Glizycms extends GlizyObject
{
    const FULLTEXT_DELIMITER = ' ## ';
    const FULLTEXT_MIN_CHAR = 2;
    private static $mediaArchiveBridge;

	function init()
	{
	    if (__Config::get('QUERY_CACHING_INIT')) {
            org_glizy_dataAccessDoctrine_DataAccess::initCache();
        }

	    glz_loadLocale( 'org.glizycms.*' );
		glz_loadLocale( 'org.glizycms.contents.*' );
        glz_loadLocale( 'org.glizycms.languages.*' );

        $application = org_glizy_ObjectValues::get('org.glizy', 'application' );

        if ($application) {
            if (!$application->isAdmin()) {
                org_glizy_ObjectFactory::remapClass('org.glizy.components.Page', 'org.glizycms.views.components.Page');
                org_glizy_ObjectFactory::remapClass('org.glizy.mvc.components.Page', 'org.glizycms.views.components.MvcPage');

                org_glizycms_userManager_fe_Module::registerModule();
            }

            // la creazione dell'istanza serve per il listener
            $speakingUrlManager = $application->registerProxy('org.glizycms.speakingUrl.Manager');

            // registra il resolver di default
            org_glizycms_speakingUrl_Manager::registerResolver(org_glizy_ObjectFactory::createObject('org.glizycms.speakingUrl.PageResolver'));
        }

        if (is_null(__Config::get('glizycms.sitemap.cacheLife'))) {
            __Config::set('glizycms.sitemap.cacheLife', 36000);
        }

        if (is_null(__Config::get('glizycms.content.history', ''))) {
            __Config::set('glizycms.content.history', true);
        }

        if (is_null(__Config::get('glizycms.content.history.comment', ''))) {
            __Config::set('glizycms.content.history.comment', false);
        }

        __Config::set('glizy.dataAccess.document.enableComment', __Config::get('glizycms.content.history.comment'));

        if (is_null(__Config::get('glizycms.pageEdit.editUrlEnabled', ''))) {
            __Config::set('glizycms.pageEdit.editUrlEnabled', true);
        }

        if (is_null(__Config::get('glizycms.mediaArchive.exifEnabled', ''))) {
            __Config::set('glizycms.mediaArchive.exifEnabled', false);
        }

        if (is_null(__Config::get('glizycms.speakingUrl', ''))) {
            __Config::set('glizycms.speakingUrl', false);
        }

        if (is_null(__Config::get('glizycms.content.showAllPageTypes', ''))) {
            __Config::set('glizycms.content.showAllPageTypes', true);
        }

        if (is_null(__Config::get('glizycms.form.actionLink.cssClass', ''))) {
            __Config::set('glizycms.form.actionLink.cssClass', 'btn action-link');
        }

        if (is_null(__Config::get('glizycms.print.enabled', ''))) {
            __Config::set('glizycms.print.enabled', false);
        }
        if (is_null(__Config::get('glizycms.print.pdf.enabled', ''))) {
            __Config::set('glizycms.print.pdf.enabled', false);
        }
        if (is_null(__Config::get('glizycms.mediaArchive.bridge', ''))) {
            __Config::set('glizycms.mediaArchive.bridge', 'org.glizycms.mediaArchive.Bridge');
        }

        self::$mediaArchiveBridge = org_glizy_ObjectFactory::createObject(__Config::get('glizycms.mediaArchive.bridge'));

        if ($application && __Config::get('glizycms.mediaArchive.mediaMappingEnabled')) {
            $application->registerProxy('org.glizycms.mediaArchive.services.MediaMappingService');
        }
	}


    static public function getSiteTemplatePath()
    {
        $templateName = org_glizy_Registry::get( __Config::get( 'REGISTRY_TEMPLATE_NAME' ), '');
        if (empty($templateName)) {
            $templateName = __Config::get('glizycms.template.default');
        }
        $templatePath = __Paths::get( 'TEMPLATE_FOLDER' );
        if ( empty( $templatePath ) ) {
            // TODO verificare perché il path è sbagliato ed è necessartio mettere ../
            $templatePath = __Paths::get( 'APPLICATION_STATIC' ).'templates/';
        }
        $templatePath .= $templateName;
        return '../'.$templatePath;
    }

    static public function getMediaArchiveBridge()
    {
        return self::$mediaArchiveBridge;
    }
}