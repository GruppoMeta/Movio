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



        self::extendConfig();

        self::$mediaArchiveBridge = org_glizy_ObjectFactory::createObject(__Config::get('glizycms.mediaArchive.bridge'));

        if ($application && __Config::get('glizycms.mediaArchive.mediaMappingEnabled')) {
            $application->registerProxy('org.glizycms.mediaArchive.services.MediaMappingService');
        }
	}


    static public function getSiteTemplatePath()
    {
        $templateName = __Config::get('glizycms.template.default');
        if (__Config::get('glizycms.contents.templateEnabled')) {
            $templateName = org_glizy_Registry::get( __Config::get( 'REGISTRY_TEMPLATE_NAME' ), $templateName);
        }

        $templatePath = __Paths::get( 'TEMPLATE_FOLDER' );
        if ( empty( $templatePath ) ) {
            // TODO verificare perché il path è sbagliato ed è necessartio mettere ../
            $templatePath = __Paths::get( 'APPLICATION_STATIC' ).'templates/';
        }
        $templatePath .= $templateName;
        return '../'.$templatePath;
    }

    /**
     * @return org_glizycms_mediaArchive_BridgeInterface
     */
    static public function getMediaArchiveBridge()
    {
        return self::$mediaArchiveBridge;
    }


    static private function extendConfig()
    {
        $config = array(
            'glizycms.sitemap.cacheLife' => 36000,
            'glizycms.content.history' => true,
            'glizycms.content.history.comment' => false,
            'glizy.dataAccess.document.enableComment' => '{{glizycms.content.history.comment}}',
            'glizycms.pageEdit.editUrlEnabled' => true,
            'glizycms.mediaArchive.exifEnabled' => false,
            'glizycms.mediaArchive.addFromZipEnabled' => false,
            'glizycms.speakingUrl' => false,
            'glizycms.content.showAllPageTypes' => true,
            'glizycms.form.actionLink.cssClass' => 'btn action-link',
            'glizycms.print.enabled' => 'false',
            'glizycms.print.pdf.enabled' => 'false',
            'glizycms.mediaArchive.bridge' => 'org.glizycms.mediaArchive.Bridge',
            'glizycms.content.draft' => false,
            'glizycms.autocompletePagePicker.limit' => 10,
            'glizycms.dublincore.enabled' => false,
            'glizycms.contents.templateEnabled' => false,
            'glizycms.mobile.template.enabled' => false,
            'glizycms.pagePicker.queryStringEnabled' => true,
        );

        foreach($config as $k=>$v) {
            if (!__Config::exists($k)) {
                __Config::set($k, $v);
            }
        }
    }
}
