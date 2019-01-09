<?php
/**
 * Application  class.
 *
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizycms_core_application_Application extends org_glizy_mvc_core_Application
{
    var $_aclPage;
    var $_siteProperty;
    var $_templateName;

    function __construct($pathApplication='', $pathCore='', $configHost='')
    {
        org_glizy_Paths::init($pathApplication, $pathCore);
        org_glizy_Paths::add('APPLICATION_TO_ADMIN', org_glizy_Paths::get('APPLICATION'));
        org_glizy_Paths::add('APPLICATION_TO_ADMIN_CACHE', org_glizy_Paths::get('APPLICATION').'../cache/');
        parent::__construct($pathApplication, $pathCore, $configHost);
    }

    public function runSoft()
    {
        parent::runSoft();
        $this->readSiteProperties();
    }

    function _init()
    {
        org_glizy_ObjectValues::set('org.glizy', 'siteId', __Config::get('glizy.multisite.id'));
        parent::_init();
    }

    function _initLanguage()
    {
        $this->log( "initLanguage", GLZ_LOG_SYSTEM );
        // inizializza la lingua
        $this->_language = org_glizy_Session::get('glizy.language', NULL);
        $this->_languageId = org_glizy_Session::get('glizy.languageId', NULL);
        if (is_null($this->_languageId))
        {
            $lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
            // try to read the browser language
            $this->log( "Read browser language", GLZ_LOG_SYSTEM );
            $ar = org_glizy_ObjectFactory::createModel('org.glizycms.core.models.Language');
            if (!$ar->find(array('language_code' => $lang))) {
                $this->log( "Read defaul language", GLZ_LOG_SYSTEM );
                $ar->emptyRecord();
                $ar->find(array('language_isDefault' => 1));
            }

            $this->_language = $ar->language_code;
            $this->_languageId = $ar->language_id;
            org_glizy_Session::set('glizy.language', $this->_language);
            org_glizy_Session::set('glizy.languageId', $this->_languageId);
        }

        org_glizy_ObjectValues::set('org.glizy', 'languageId', $this->_languageId);

        // importa i file di localizzazione
        if (file_exists(org_glizy_Paths::getRealPath('CORE_CLASSES').'org/glizy/locale/'.$this->getLanguage().'.php'))
        {
            $this->log( "Import locale file", GLZ_LOG_SYSTEM );
            require_once(org_glizy_Paths::getRealPath('CORE_CLASSES').'org/glizy/locale/'.$this->getLanguage().'.php');
        } else {
            require_once(org_glizy_Paths::getRealPath('CORE_CLASSES').'org/glizy/locale/en.php');
        }
    }

    function createSiteMap($forceReload=false)
    {
        $this->log( "initSiteMap", GLZ_LOG_SYSTEM );
        $this->siteMap = &org_glizy_ObjectFactory::createObject('org.glizycms.core.application.SiteMapDB');
        $this->siteMap->getSiteArray($forceReload);

        // controlla se l'utente ha i permessi per modificare la pagina
        // per velocizzare vengono precaricate tutte le relazioni in memoria
        $this->_aclPage = array();
        if ( __Config::get( 'ACL_ENABLED' ) )
        {

            $this->_aclPage = __Session::get('glizy.aclFront', NULL);
            if (is_null($this->_aclPage)) {
                $this->_aclPage = array();
                $it = org_glizy_ObjectFactory::createModelIterator( 'org.glizy.models.Join', 'all', array( 'filters' => array( 'join_objectName' => 'menus_tbl#rel_aclFront' ) ) );
                foreach ($it as $arC) {
                    if ( !isset( $this->_aclPage[ $arC->join_FK_source_id ] ) )
                    {
                        $this->_aclPage[ $arC->join_FK_source_id ] = array();
                    }
                    $this->_aclPage[ $arC->join_FK_source_id ][] = $arC->join_FK_dest_id ;
                }

                // scorre tutti i menù per attribuire l'acl ai menù che non ce l'hanno
                // ereditandola dal padre
                $siteMapIterator = &org_glizy_ObjectFactory::createObject('org.glizy.application.SiteMapIterator', $this->siteMap);
                while (!$siteMapIterator->EOF) {
                    $n = $siteMapIterator->getNode();
                    $siteMapIterator->moveNext();

                    if ( !isset($this->_aclPage[$n->id])) {
                        $n2 = $n;
                        while (true) {
                            if ( $n2->parentId == 0 ) break;
                            $parentNode =  $n2->parentNode();
                            $n2 = $parentNode;
                            if ( isset( $this->_aclPage[$parentNode->id])) {
                                $this->_aclPage[$n->id] = $this->_aclPage[$parentNode->id];
                                break;
                            }
                        }
                    }
                }
                __Session::set('glizy.aclFront', $this->_aclPage);
            }
        }
    }

    function _startProcess($readPageId=true)
    {
        $this->log( "startProcess", GLZ_LOG_SYSTEM );
        $this->checkSwitchLanguage();

        $controller = __Request::get( 'controller', '', GLZ_REQUEST_ROUTING );
        if ($controller)
        {
            $controllerClass = org_glizy_ObjectFactory::createObject( $controller, null, $this );
            org_glizy_helpers_PhpScript::callMethodWithParams( $controllerClass, 'execute' );
        }

        $this->readSiteProperties();
        $this->setTemplateFolder();
        if ($readPageId) {
            $evt = array('type' => GLZ_EVT_BEFORE_CREATE_PAGE);
            $this->dispatchEvent($evt);
            $this->_readPageId();
        }

        if ($this->siteMapMenu->isVisible===false)
        {
            while(true)
            {
                $parentMenu = &$this->siteMapMenu->parentNode();

                if (is_null($parentMenu))
                {
                    // ERROR
                    $e = new org_glizy_Exception(array('[%s] %s', $this->getClassName(), __T(GLZ_ERR_EMPTY_APP_PATH)));
                }

                $this->siteMapMenu = &$parentMenu;
                if ($parentMenu->isVisible===true)
                {
                    $this->_pageId = $this->siteMapMenu->id;
                    break;
                }
            }
        }

        // TODO da risolvare in modo migliore
        // creando un nuovo menu_type

        if ($this->siteMapMenu->pageType=='Empty') {
            $currentPage = &$this->siteMapMenu;
            $childPos = 0;
            while (true) {
                $childNodes = $currentPage->childNodes();
                if (!count($childNodes)) {
                    org_glizy_helpers_Navigation::gotoUrl(GLZ_HOST);
                    return;
                }

                $tempPage = &$childNodes[$childPos];
                if ($tempPage->type=='BLOCK') {
                    $childPos++;
                    continue;
                }

                $currentPage = &$tempPage;
                $childPos = 0;
                if ($currentPage->pageType!='Empty') {
                    $this->siteMapMenu = &$currentPage;
                    $this->_pageId = $currentPage->id;
                    break;
                }
            }
        }

        parent::_startProcess(false);
    }

    function canViewPage( $page=null )
    {
        if ( is_null( $page ) )
        {
            $page = $this->_pageId;
        }

        $user = &$this->getCurrentUser();

        /*
        if ( isset( $this->_aclPage[ $page ] ) && $user->groupId != 1 )
        {
            return in_array( $user->groupId, $this->_aclPage[ $page ] );
        }
        else return true;
        */

        if (__Config::get('ACL_ROLES')) {
            if ( isset( $this->_aclPage[ $page ] ) && !$user->acl(__Config::get('SITEMAP_ID'), 'all')) {
                return $user->isInRoles($this->_aclPage[ $page ]);
            } else {
                return true;
            }
        } else {
            if ( isset( $this->_aclPage[ $page ]) && $user->groupId != 1 ) {
                return in_array( $user->groupId, $this->_aclPage[ $page ] );
            } else {
                return true;
            }
        }
    }

    function getSiteProperty()
    {
        return $this->_siteProperty;
    }

    function getTemplateName()
    {
        return $this->_templateName;
    }


    private function setTemplateFolder()
    {
        $this->_templateName = __Config::get('glizycms.template.default');
        if (__Config::get('glizycms.contents.templateEnabled')) {
            $this->_templateName = org_glizy_Registry::get( __Config::get( 'REGISTRY_TEMPLATE_NAME' ), $this->_templateName);
        }

        if (__Config::get('glizycms.mobile.template.enabled')) {
            $browser = strpos($_SERVER['HTTP_USER_AGENT'],"iPhone") || strpos($_SERVER['HTTP_USER_AGENT'],"Android");
            if ($browser === true) {
                if ( file_exists( org_glizy_Paths::get('APPLICATION_STATIC').'templates/'.$this->_templateName.'-mobile' ) ) {
                    $this->_templateName .= '-iPhone';
                }
                else if ( file_exists( org_glizy_Paths::get('APPLICATION_STATIC').'templates/mobile' ) )
                {
                    $this->_templateName = 'iPhone';
                }
            }
        }

        $pathBaseTemplate = org_glizy_Paths::get('APPLICATION_STATIC').'templates/';
        org_glizy_Paths::set('APPLICATION_TEMPLATE', $pathBaseTemplate.$this->_templateName.'/');
        org_glizy_Paths::set('APPLICATION_TEMPLATE_DEFAULT', $pathBaseTemplate.'Default/');
        org_glizy_Paths::addClassSearchPath($pathBaseTemplate.$this->_templateName.'/classes/');
        glz_loadLocaleReal( $pathBaseTemplate.$this->_templateName.'/classes', $this->getLanguage() );
    }

    private function readSiteProperties()
    {
        $siteProp = unserialize(org_glizy_Registry::get(__Config::get( 'REGISTRY_SITE_PROP' ).$this->getLanguage(), ''));
        if (!is_array($siteProp))
        {
            // if the site properties are not defined
            // try to read the properties from default language
            $ar = org_glizy_ObjectFactory::createModel('org.glizycms.core.models.Language');
            $ar->language_isDefault = 1;
            $ar->find();
            $siteProp = org_glizy_Registry::get(__Config::get( 'REGISTRY_SITE_PROP' ).$ar->language_code, '');
            org_glizy_Registry::set(__Config::get( 'REGISTRY_SITE_PROP' ).$this->getLanguage(), $siteProp);
            $siteProp = unserialize($siteProp);
        }
        if (!is_array($siteProp))
        {
            $siteProp = array();
        }
        $this->_siteProperty = $siteProp;
    }

    private function checkSwitchLanguage()
    {
        $language = org_glizy_Request::get('language', NULL);

        if (!is_null($language) && $language!=$this->_language)
        {
            // cambio lingua
            $this->log( "change language", GLZ_LOG_SYSTEM );
            $ar = org_glizy_ObjectFactory::createModel('org.glizycms.core.models.Language');
            $ar->language_code = $language;
            $ar->find();

            org_glizy_Session::set('glizy.language', $ar->language_code);
            org_glizy_Session::set('glizy.languageId', $ar->language_id);
            org_glizy_ObjectValues::set('org.glizy', 'languageId', $ar->language_id);
            $this->_languageId = $ar->language_id;
            $this->_language = $ar->language_code;

            // ricarica la struttura del sito per avere i titoli aggiornati
            $this->_initSiteMap(true);

            // controlla se il routing ha definito un urlResolver
            $speakingUrlManager = $this->retrieveProxy('org.glizycms.speakingUrl.Manager');
            $urlResolver = $speakingUrlManager->getResolver(__Request::get('cms:urlResolver', 'org.glizycms.core.models.Content'));
            $url = $urlResolver->makeUrlFromRequest();

            org_glizy_helpers_Navigation::gotoUrl($url);
        }
    }
}
