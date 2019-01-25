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

class org_glizy_application_Application extends GlizyObject
{
    var $_pathCore                = '';
    var $_pathApplication        = '';
    var $_pageId                = 0;
    var $_pageType                = '';
    var $outputMode                = 'html';
    /** @var org_glizy_application_SiteMapSimple $siteMap */
    var $siteMap                = NULL;
    /** @var org_glizy_application_SiteMapNode $siteMapMenu */
    var $siteMapMenu            = NULL;
    /** @var org_glizy_components_ComponentContainer $_rootComponent */
    var $_rootComponent            = NULL;
    /** @var org_glizy_application_User $_user */
    var $_user                    = NULL;
    var $_language                = '';
    var $_languageId            = 0;
    var $_ajaxMode                = false;
    var $_logObj                =     NULL;
    var $_configHost;
    protected $contentType         = 'text/html';

    /**
     * @param string $pathApplication
     * @param string $pathCore
     * @param string $configHost
     */
    function __construct($pathApplication='', $pathCore='', $configHost='')
    {
        if (empty($pathApplication))
        {
            new org_glizy_Exception(array('[%s] %s', $this->getClassName(), GLZ_ERR_EMPTY_APP_PATH));
        }

        org_glizy_ObjectValues::setByReference('org.glizy', 'application', $this);
        $this->_pathApplication = $pathApplication;
        $this->_pathCore         = $pathCore;
        $this->_configHost         = $configHost;
        $this->addEventListener(GLZ_EVT_USERLOGIN, $this);
        $this->addEventListener(GLZ_EVT_USERLOGOUT, $this);
        $this->_init();
    }


    function run()
    {
        $this->log( "Run application", GLZ_LOG_SYSTEM );
        if (file_exists(org_glizy_Paths::get('APPLICATION_STARTUP')))
        {
            // if the startup folder is defined all files are included
            glz_require_once_dir(org_glizy_Paths::get('APPLICATION_STARTUP'));
        }

        glz_defineBaseHost();
        $this->login();
        $this->_initSiteMap();
        $this->_initRequest();

        org_glizy_ObjectValues::set('org.glizy.og', 'url', GLZ_HOST.'/go/'.__Request::get('__url__') );
        glz_require_once_dir(org_glizy_Paths::getRealPath('APPLICATION_CLASSES'));

        $this->_startProcess();

        if (file_exists(org_glizy_Paths::get('APPLICATION_SHUTDOWN')))
        {
            // if the shutdown folder is defined all files are included
            glz_require_once_dir(org_glizy_Paths::get('APPLICATION_SHUTDOWN'));
        }
    }

    function runAjax()
    {
        $this->log( "Run ajax application", GLZ_LOG_SYSTEM );
        org_glizy_Request::$translateInfo = false;
        org_glizy_Request::$skipDecode = __Config::get( 'AJAX_SKIP_DECODE' );

        $this->_ajaxMode = true;
        $this->run();
    }

    function runSoft()
    {
        $this->log( "Run application (soft mode)", GLZ_LOG_SYSTEM );

        if (file_exists(org_glizy_Paths::get('APPLICATION_STARTUP')))
        {
            // if the startup folder is defined all files are included
            glz_require_once_dir(org_glizy_Paths::get('APPLICATION_STARTUP'));
        }

        glz_defineBaseHost();
        $this->login();
        $this->_initSiteMap();
        $this->_initRequest();

        if (file_exists(org_glizy_Paths::get('APPLICATION_SHUTDOWN')))
        {
            // if the shutdown folder is defined all files are included
            glz_require_once_dir(org_glizy_Paths::get('APPLICATION_SHUTDOWN'));
        }
    }

    function stop()
    {
        org_glizy_Paths::destroy();
        org_glizy_Config::destroy();
        org_glizy_Request::destroy();
        org_glizy_Routing::destroy();
        org_glizy_ObjectValues::removeAll();
    }

    function _init()
    {
        // inizializzazione delle classi
        // classe statica per la gestione dei path
        org_glizy_Paths::init($this->_pathApplication, $this->_pathCore);

        // legge i parametri di configurazione
        org_glizy_Config::init( $this->_configHost );
        $this->setExceptionParams();

        $sessionPrefix = org_glizy_Config::get('SESSION_PREFIX');
        if (empty($sessionPrefix))
        {
            // se non è stato specificato un prefisso per la sessione
            // viene usato il nome dell'applicazione
            org_glizy_Config::set('SESSION_PREFIX', str_replace(array('.', ' ', '/'), '', $this->_pathApplication).'_');
        }

        // inizializzazione della sessione
        __Session::start();
        if (org_glizy_config::get('LOG_FILE')!='')
        {
            if ( org_glizy_config::get('LOG_FILE') != "firebug" )
            {
                $this->_logObj = org_glizy_log_LogFactory::create('File', org_glizy_Paths::get('APPLICATION').'/'.org_glizy_config::get('LOG_FILE'), array(), org_glizy_config::get('LOG_LEVEL'));
            }
            else
            {
                $this->_logObj = org_glizy_log_LogFactory::create('FireBug', array(), org_glizy_config::get('LOG_LEVEL'));
            }
            $this->log( "Start application", GLZ_LOG_SYSTEM );
        }

		if (__Config::get ( 'glizy.exception.log.format' )=='elasticsearch') {
			org_glizy_log_LogFactory::create('ElasticSearch', array(), 0); // Questo serve per poter istanziare la classe in Exception.php (gruppo 0 non logga nulla)
		}

        $this->_initLanguage();
    }

    function _initRequest()
    {
        org_glizy_Routing::init();
        org_glizy_Routing::_parseUrl();
        org_glizy_Request::init();
    }


    function _initLanguage()
    {
        $this->log( "initLanguage", GLZ_LOG_SYSTEM );
        $currentLanguage = __Session::get('glizy.language', __Config::get('DEFAULT_LANGUAGE'));
        // NOTA: __Request non è ancora inizializzata
        // per risolvere il problema che usandolo viene inizializzata
        // prima del run o runSoft() creando problemi nella translateInfo
        // leggo direttamente dalla _GET
        // Da risolvere cambiando l'ordine di inizializzazione delle varie componenti
        $language = isset($_GET['language']) ? $_GET['language'] : null; // __Request::get('language', NULL);

        if ($language && $language!=$currentLanguage) {
            // cambio lingua controlla se la lingua richiesta è tra quelle accettate
            $availableLanguages = explode(',', __Config::get('glizy.languages.available'));
            if (in_array($language, $availableLanguages)) {
               $currentLanguage = $language;
            }
        }

        $this->_language = $currentLanguage;
        // NOTA non viene supportato l'id numerico della lingua
        $this->_languageId = __Session::get('glizy.languageId',  __Config::get('DEFAULT_LANGUAGE_ID'));
        org_glizy_ObjectValues::set('org.glizy', 'language', $this->_language);
        org_glizy_ObjectValues::set('org.glizy', 'languageId', $this->_languageId);
        org_glizy_Session::set('glizy.language', $this->_language);
        org_glizy_Session::set('glizy.languageId', $this->_languageId);
        $this->_loadLocale();
    }

    /**
     * @param bool $forceReload
     */
    function createSiteMap($forceReload=false)
    {
        $this->log( "initSiteMap", GLZ_LOG_SYSTEM );
        $this->siteMap = &org_glizy_ObjectFactory::createObject('org.glizy.application.SiteMapSimple');
        $this->siteMap->getSiteArray($forceReload);
    }

    function _readPageId()
    {
        $this->log( "readPageId", GLZ_LOG_SYSTEM );
        // legge il pageId della pagina da visualizzare
        $this->_pageId = org_glizy_Request::get('pageId', NULL);
        $url = org_glizy_Request::get('__url__', NULL);

        if ((!$this->_pageId && __Request::exists('__routingPattern__')) || (!$this->_pageId && !$url))
        {
            $this->_pageId =  org_glizy_Config::get('REMEMBER_PAGEID') ? org_glizy_Session::get('glizy.pageId', org_glizy_Config::get('START_PAGE')) : org_glizy_Config::get('START_PAGE');
        }

        // TODO: rimuovere questa specializzazione per il cms
        if (!is_numeric($this->_pageId) && ( $this->getClassName()=='org_glizycms_core_application_application') )
        {
            $this->siteMapMenu    = &$this->siteMap->getMenuByPageType($this->_pageId);
            $this->_pageId        = $this->siteMapMenu->id;
        }
        else
        {
            $this->siteMapMenu    = &$this->siteMap->getNodeById($this->_pageId);
        }

        if ($this->siteMapMenu->hideByAcl) {
            org_glizy_helpers_Navigation::accessDenied($this->getCurrentUser()->isLogged());
        }

        if (!is_object($this->siteMapMenu) || !$this->siteMapMenu->isVisible)
        {
            $evt = array('type' => GLZ_EVT_DUMP_404);
            $this->dispatchEvent($evt);

            if ($this->siteMapMenu && !$this->getCurrentUser()->acl($this->siteMapMenu->id, "visible", true)) {
                org_glizy_helpers_Navigation::gotoUrl( __Link::makeUrl( 'link', array( 'pageId' => __Config::get('START_PAGE'))));
            }
            $error404Page = __Config::get( 'ERROR_404');
            if ( !empty( $error404Page ) )
            {
                org_glizy_helpers_Navigation::gotoUrl( __Link::makeUrl( 'link', array( 'pageId' => $error404Page ) ) );
            }
            org_glizy_helpers_Navigation::notFound();
        }

        if (!empty($this->siteMapMenu->select)) {
            if ($this->siteMapMenu->select=='*') {
                $menu = $this->siteMapMenu->firstChild(true);
            } else {
                $menu = $this->siteMap->getNodeById($this->siteMapMenu->select);
            }
            org_glizy_helpers_Navigation::gotoUrl( __Link::makeUrl( 'link', array( 'pageId' => $menu->id ) ) );
        }

        if (org_glizy_Config::get('REMEMBER_PAGEID'))
        {
            org_glizy_Session::set('glizy.pageId', $this->_pageId);
        }

    }

    /**
     * @param bool $readPageId
     */
    function _startProcess($readPageId=true)
    {
        $middlewareObj = null;

        $this->log( "startProcess", GLZ_LOG_SYSTEM );
        if ( $this->_logObj )
        {
            $this->log( array( 'Request' => __Request::getAllAsArray() ), GLZ_LOG_SYSTEM );
        }

        if ($readPageId) {
            $evt = array('type' => GLZ_EVT_BEFORE_CREATE_PAGE);
            $this->dispatchEvent($evt);
            $this->_readPageId();
        }

        org_glizy_ObjectValues::set('org.glizy.application', 'pageId', $this->_pageId);
        $this->_pageType = $this->siteMapMenu->pageType;

        if (__Request::exists('__middleware__')) {
            $middlewareObj = org_glizy_ObjectFactory::createObject(__Request::get('__middleware__'));
            // verify the cache before page rendering
            // this type of cache is available only for Static Page
            if ($middlewareObj) {
                $middlewareObj->beforeProcess($this->_pageId, $this->_pageType);
            }
        }

        org_glizy_ObjectFactory::createPage( $this, $this->_pageType, null, array( 'pathTemplate' => org_glizy_Paths::get('APPLICATION_TEMPLATE') ) );

        if (!is_null($this->_rootComponent))
        {
            if (!$this->_ajaxMode)
            {
                // serve per resettare lo stato del sessionEx ad ogni caricamento delle pagine
                // altrimenti gli stati vecchi non vengono cancellati
                // quando c'è un cambio di pagina e SessionEx non è usato
                org_glizy_ObjectFactory::createObject('org.glizy.SessionEx', '');

                $this->_rootComponent->resetDoLater();
                $this->_rootComponent->init();
                $this->_rootComponent->execDoLater();

                $this->log( "Process components", GLZ_LOG_SYSTEM );
                $this->_rootComponent->resetDoLater();

                $evt = array('type' => GLZ_EVT_START_PROCESS);
                $this->dispatchEvent($evt);

                if (method_exists($this, 'process_onStart')) $this->process_onStart();
                $this->_rootComponent->process();
                if (method_exists($this, 'process_onEnd')) $this->process_onEnd();
                $this->_rootComponent->execDoLater();

                $evt = array('type' => GLZ_EVT_END_PROCESS);
                $this->dispatchEvent($evt);


                // check if enable the PDF output
                if ( $this->getCurrentMenu()->printPdf )
                {
                    $pdfPage = org_glizy_Paths::getRealPath('APPLICATION_TEMPLATE', 'pdf.php' );
                    if ( $pdfPage !== false )
                    {
                        if ( __Request::get( 'printPdf', '0' ) )
                        {
                            org_glizy_ObjectValues::set( 'org.glizy.application', 'pdfMode', __Request::get( 'printPdf', '0' ) == '1' );
                        }
                    }
                    else
                    {
                        $this->getCurrentMenu()->printPdf = false;
                    }
                }

                $evt = array('type' => GLZ_EVT_START_RENDER);
                $this->dispatchEvent($evt);
                $this->_rootComponent->resetDoLater();
                if (method_exists($this, 'render_onStart')) $this->render_onStart();

                $this->addJScoreLibraries();

                $output = $this->_rootComponent->render();

                if (method_exists($this, 'render_onEnd')) $this->render_onEnd();
                $this->_rootComponent->execDoLater();

                $evt = array('type' => GLZ_EVT_END_RENDER);
                $this->dispatchEvent($evt);

                $headerErrorCode = __Request::get( 'glizyHeaderCode', '' );
                if ( $headerErrorCode )
                {
					$message = $headerErrorCode.' '.org_glizy_helpers_HttpStatus::getStatusCodeMessage( (int)$headerErrorCode );
					header( "HTTP/1.1 ".$message );
					header( "Status: ".$message );
                }
                header("Content-Type: ".$this->contentType."; charset=".__Config::get('CHARSET'));

                if ($middlewareObj) {
                    // verify the cache after content rendering
                    $middlewareObj->afterRender($output);
                }

                echo $output;
            }
            else
            {
                $this->startProcessAjax();
            }
        }
        else
        {
            // TODO
            // visualizzare errore
        }
    }

    private function startProcessAjax()
    {
        header('Cache-Control: no-cache');
        header('Pragma: no-cache');
        header('Expires: -1');

        $this->_rootComponent->resetDoLater();
        $this->_rootComponent->init();
        $this->_rootComponent->execDoLater();

        $evt = array('type' => GLZ_EVT_START_PROCESS);
        $this->dispatchEvent($evt);

        $acl = $this->_rootComponent->getAttribute( 'acl' );
        if ($acl) {
            list( $service, $action ) = explode( ',', $acl );
            if (!$this->_user->acl($service, $action, false)) {
                org_glizy_helpers_Navigation::accessDenied(false);
            }
        }

        $ajaxTarget = org_glizy_Request::get('ajaxTarget');
        $targetComponent = &$this->_rootComponent->getComponentById($ajaxTarget);
        if (is_null($targetComponent)) {
            // prima prova a creare i figli in modo ritardato
            // questo è usato nella gestione degli stati
            $this->_rootComponent->deferredChildCreation(true);
            $targetComponent = &$this->_rootComponent->getComponentById($ajaxTarget);


            // se il targetComponent è ancora nullo
            // prova a lanciare il process di tutti i figli
            if (is_null($targetComponent)) {
                $this->_rootComponent->process();
                $targetComponent = &$this->_rootComponent->getComponentById($ajaxTarget);
                if (is_null($targetComponent)) {
                    return false;
                }
            }
        }

        $ajaxMethod = __Request::get('ajaxMethod', 'process_ajax');
        if (method_exists($targetComponent, $ajaxMethod)) {
            org_glizy_Request::remove('pageId');
            org_glizy_Request::remove('ajaxTarget');
            $result = $targetComponent->{$ajaxMethod}();
        } else {
            $result = $this->processAjaxCallController($targetComponent);
        }

        if (!$targetComponent->controllerDirectOutput() && !is_array($result) && !is_object($result)) $result = array('status'=> ($result===true ? 'success' : 'error'));
        if ( is_array($result) && isset( $result['html'] ) ) {
            header("Content-Type: ".$this->contentType."; charset=".__Config::get('CHARSET'));
            echo $result['html'];
        } else {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode( $result );
        }
        return true;
    }

    /**
     * @param org_glizy_components_Component $targetComponent
     *
     * @return array
     */
    private function processAjaxCallController($targetComponent)
    {
        if ( __Request::exists('controllerName')) {
            $targetComponent->setAttribute('controllerName', __Request::get('controllerName'));
        }
        $result = array( 'status' => false );
        $r = $targetComponent->callController();
        if ($r !== null && $r !== false)
        {
            if ( $targetComponent->controllerDirectOutput() ) return $r;

            $result['status'] = true;
            if ( is_array( $r ) && isset( $r[ 'error' ] ) )
            {
                $result['status'] = false;
                $result['error'] = $r[ 'error' ];
                return $result;
            }

            $outputFormatInHtml = false;
            $html = '';

            if ( is_array( $r ) && isset( $r[ 'sendOutput' ] ) )
            {
                // controlla se c'è renderizzare dei componenti da mandare all'output
                __Request::set('action', isset($r['sendOutputState']) ? $r['sendOutputState'] : '');
                $outputFormatInHtml = isset( $r[ 'sendOutputFormat' ] ) && $r[ 'sendOutputFormat' ] == 'html';
                $this->_rootComponent->process();

                $componentsId = $r[ 'sendOutput' ];
                unset( $r[ 'sendOutput' ] );
                unset( $r[ 'sendOutputState' ] );
                if ( !is_array( $componentsId ) ) {
                    $componentsId = array( $componentsId );
                }

                foreach( $componentsId as $id ) {
                    $c = $this->_rootComponent->getComponentById( $id );
                    if ( is_object( $c ) ) {
                        $this->_rootComponent->_output = array();
                        $c->render();

                        //$r[ 'sendOutput' ][ $id ] = $this->_output[ 0 ][ 'code' ];
                        $r[ $id ] = '';
                        foreach( $this->_rootComponent->_output as $o ) {
                            if ( strpos($o['editableRegion'], '__') !== false ) continue;
                            $r[ $id ] .= $o[ 'code' ];
                            $html .= $o[ 'code' ];
                        }
                    }
                }
            }

            if ( $outputFormatInHtml ) {
                $result['html'] = $html;
            } else {
                $result['result'] = $r;
            }
        }
        return $result;
    }

    /**
     * @param org_glizy_components_Component $component
     */
    function addChild(&$component)
    {
        $this->_rootComponent = &$component;
    }

    /**
     * @return org_glizy_components_Component
     */
    function &getRootComponent()
    {
        return $this->_rootComponent;
    }

    /**
     * @return string
     */
    function getOutputMode()
    {
        return $this->outputMode;
    }

    /**
     * @return string
     */
    function getPageId()
    {
        return strtolower( $this->_pageId );
    }

    /**
     * @param string $id
     */
    function setPageId($id)
    {
        $this->_pageId = $id;
        $this->siteMapMenu = &$this->siteMap->getNodeById($this->_pageId);
    }

    /**
     * @return string
     */
    function getPageType()
    {
        return $this->_pageType;
    }

    /**
     * @return org_glizy_application_SiteMapNode
     */
    function &getCurrentMenu()
    {
        return $this->siteMapMenu;
    }

    /**
     * @return org_glizy_application_User
     */
    function &getCurrentUser()
    {
        $user = &org_glizy_ObjectValues::get('org.glizy', 'user');
        return $user;
    }

    /**
     * @return org_glizy_application_SiteMapSimple
     */
    function &getSiteMap()
    {
        return $this->siteMap;
    }

    /**
     * @return int
     */
    function getLanguageId()
    {
        return $this->_languageId;
    }

    /**
     * @param int $value
     */
    function setLanguageId($value)
    {
        $this->_languageId = $value;
        org_glizy_ObjectValues::set('org.glizy', 'languageId', $this->_languageId);
    }

    /**
     * @return string
     */
    function getLanguage()
    {
        return strtolower($this->_language);
    }

    /**
     * @param $value
     */
    function setLanguage($value)
    {
        $value = strtolower($value);
        if ($this->_language != $value)
        {
            $this->_language = $value;
            $this->_loadLocale();
        }
    }

    function _loadLocale()
    {
        // importa i file di localizzazione
        if (file_exists(org_glizy_Paths::getRealPath('CORE_CLASSES').'org/glizy/locale/'.$this->getLanguage().'.php'))
        {
            require(org_glizy_Paths::getRealPath('CORE_CLASSES').'org/glizy/locale/'.$this->getLanguage().'.php');
        }
        else
        {
            require(org_glizy_Paths::getRealPath('CORE_CLASSES').'org/glizy/locale/en.php');
        }
    }

    function addJScoreLibraries()
    {
        if (!org_glizy_ObjectValues::get('org.glizy.JS.Core', 'add', false) && __Config::get( 'GLIZY_ADD_CORE_JS' ) )
        {
            org_glizy_ObjectValues::set('org.glizy.JS.Core', 'add', true);
            $this->addJSLibCore();
            if (__Config::get('DEBUG')) {
                $this->_rootComponent->addOutputCode(org_glizy_helpers_JS::linkCoreJSfile('dejavu/strict/dejavu.js?v='.GLZ_CORE_VERSION), 'head');
            } else {
                $this->_rootComponent->addOutputCode(org_glizy_helpers_JS::linkCoreJSfile('dejavu/loose/dejavu.min.js?v='.GLZ_CORE_VERSION), 'head');
            }
            $this->_rootComponent->addOutputCode(org_glizy_helpers_JS::linkCoreJSfile('Glizy.js?v='.GLZ_CORE_VERSION), 'head');
            $filename = $this->getLanguage().'.js';
            if(!file_exists(__DIR__."/../../../../static/js/locale/".$filename)) {
                $filename = 'en.js';
            }
            $this->_rootComponent->addOutputCode(org_glizy_helpers_JS::linkCoreJSfile($filename, 'locale/'), 'head');
        }
    }

    function addJSLibCore()
    {
        if (!org_glizy_ObjectValues::get('org.glizy.JS.lib', 'add', false) && __Config::get( 'GLIZY_ADD_JS_LIB' ) )
        {
            org_glizy_ObjectValues::set('org.glizy.JS.lib', 'add', true);
            if ( __Config::get( 'GLIZY_ADD_JQUERY_JS' ) )
            {
                $this->_rootComponent->addOutputCode(org_glizy_helpers_JS::linkStaticJSfile( 'jquery/' . __Config::get('GLIZY_JQUERY' ) ), 'head');
                if ( __Config::get( 'GLIZY_ADD_JQUERYUI_JS' ) )
                {
                    $this->_rootComponent->addOutputCode( org_glizy_helpers_JS::linkStaticJSfile( 'jquery/jquery-ui/' . __Config::get('GLIZY_JQUERYUI' ) ), 'head');
                    $this->_rootComponent->addOutputCode( org_glizy_helpers_CSS::linkStaticCSSfile( 'jquery/jquery-ui/' . __Config::get('GLIZY_JQUERYUI_THEME' ) ), 'head');
                }
            }
        }
    }

    function addLightboxJsCode()
    {
        if (!org_glizy_ObjectValues::get('org.glizy.JS.Lightbox', 'add', false) && __Config::get( 'GLIZY_ADD_JS_LIB' ) )
        {
			$colorboxSlideshowAuto = __Config::get('COLORBOX_SLIDESHOWAUTO');
			$colorboxSlideshowAuto = $colorboxSlideshowAuto ? 'true' : 'false';
			org_glizy_ObjectValues::set('org.glizy.JS.Lightbox', 'add', true);
            $this->addJSLibCore();

            $this->_rootComponent->addOutputCode( org_glizy_helpers_CSS::linkStaticCSSfile('jquery/colorbox/glizy/colorbox.css' ), 'head' );
            $this->_rootComponent->addOutputCode( org_glizy_helpers_JS::linkStaticJSfile('jquery/colorbox/jquery.colorbox-min.js' ), 'head' );
            $this->_rootComponent->addOutputCode(org_glizy_helpers_JS::JScode( 'jQuery(document).ready(function() { jQuery("a.js-lightbox-image").colorbox({ photo:true, slideshow:true, slideshowAuto:'.$colorboxSlideshowAuto.', slideshowSpeed: Glizy.slideShowSpeed, current: "{current} di {total}",
        previous: "'.__T('GLZ_PREVIOUS').'",
        next: "'.__T('GLZ_NEXT').'",
        close: "'.__T('GLZ_COLSE').'",
        slideshowStart: "'.__T('GLZ_SLIDESHOW_START').'",
        slideshowStop: "'.__T('GLZ_SLIDESHOW_STOP').'" })  });' ), 'head');

            $this->_rootComponent->addOutputCode(org_glizy_helpers_JS::JScode( 'jQuery(document).ready(function() { jQuery("a.js-lightbox-inline").colorbox({inline:true, title: false})});' ), 'head');
        }
    }

    function addZoomJsCode()
    {
        if (!org_glizy_ObjectValues::get('org.glizy.JS.Zoom', 'add', false) && __Config::get( 'GLIZY_ADD_JS_LIB' ) )
        {
            org_glizy_ObjectValues::set('org.glizy.JS.Zoom', 'add', true);
            $this->addJSLibCore();
            $this->_rootComponent->addOutputCode( org_glizy_helpers_JS::linkStaticJSfile('OpenSeadragon/OpenSeadragon.js' ), 'head' );
            $this->_rootComponent->addOutputCode( '<div id="zoomContainer" data-cache="'.__Paths::get('CACHE').'"></div>' );
        }
    }

    /**
     * @param string $formName
     */
    function addValidateJsCode( $formName=null )
    {
        if (!__Config::get('GLIZY_ADD_VALIDATE_JS')) return;
        if (!org_glizy_ObjectValues::get('org.glizy.JS.Validate', 'add', false) && __Config::get( 'GLIZY_ADD_JS_LIB' ) )
        {
            // Validate
            org_glizy_ObjectValues::set('org.glizy.JS.Validate', 'add', true);
            $this->addJSLibCore();

            if ( file_exists( org_glizy_Paths::get('STATIC_DIR').'jquery/jquery.validationEngine/jquery.validationEngine-'.$this->getLanguage().'.js' ) )
            {
                $this->_rootComponent->addOutputCode(org_glizy_helpers_JS::linkStaticJSfile( 'jquery/jquery.validationEngine/jquery.validationEngine-'.$this->getLanguage().'.js' ), 'head');
            }else {
                $this->_rootComponent->addOutputCode(org_glizy_helpers_JS::linkStaticJSfile( 'jquery/jquery.validationEngine/jquery.validationEngine-en.js' ), 'head');
            }
            $this->_rootComponent->addOutputCode( org_glizy_helpers_JS::linkStaticJSfile( 'jquery/jquery.validationEngine/jquery.validationEngine.js' ), 'head');
            $this->_rootComponent->addOutputCode( org_glizy_helpers_CSS::linkStaticCSSfile( 'jquery/jquery.validationEngine/validationEngine.jquery.css' ), 'head');
        }

        if ( !is_null( $formName ) && __Config::get( 'GLIZY_ADD_JS_LIB' ) )
        {
                $this->_rootComponent->addOutputCode(org_glizy_helpers_JS::JScode( 'jQuery(document).ready(function() { $("#'.$formName.'").validationEngine( "attach", { validationEventTrigger: "none", scroll: false, showAllErrors: false } ); });' ), 'head');

//                $this->_rootComponent->addOutputCode(org_glizy_helpers_JS::JScode( '$(document).ready(function() { $("#'.$formName.'").validationEngine(); });' ), 'head');
        }
    }

    /* events listener */
    function login()
    {
        $this->log( "login", GLZ_LOG_SYSTEM );
        if (org_glizy_Session::get('glizy.userLogged'))
        {
            $this->log( "user is logged", GLZ_LOG_SYSTEM );
            $user = org_glizy_Session::get('glizy.user');

            // crea l'utente
            $this->_user = &org_glizy_ObjectFactory::createObject('org.glizy.application.User', $user);
            org_glizy_ObjectValues::setByReference('org.glizy', 'user', $this->_user);
            org_glizy_ObjectValues::set('org.glizy', 'userId', $this->_user->id);

            if (org_glizy_Config::get('USER_LOG'))
            {
                $this->log( "log user access", GLZ_LOG_SYSTEM );
                $arLog = &org_glizy_ObjectFactory::createModel('org.glizy.models.UserLog');
                $arLog->load($user['logId']);
                $arLog->userlog_FK_user_id = $user['id'];
                $arLog->save();
            }
        }
        else
        {
            $this->createDummyUser();
        }
        }

    public function onLogout()
    {
        // create dummy user
        $this->createDummyUser();
    }

    /**
     * @return bool
     */
    function isAdmin()
    {
        return false;
    }

    /**
     * @return bool
     */
    function canViewPage()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isAjaxMode()
    {
        return $this->_ajaxMode;
    }


    private function createDummyUser()
    {
        // create dummy user
        $user = 0;
        $this->_user = &org_glizy_ObjectFactory::createObject('org.glizy.application.User', $user);
        org_glizy_ObjectValues::setByReference('org.glizy', 'user', $this->_user);
        org_glizy_ObjectValues::set('org.glizy', 'userId', 0);
    }

    protected $sitemapFactory = null;

    public function sitemapFactory($factory)
    {
        $this->sitemapFactory = $factory;
    }

    function _initSiteMap($forceReload=false)
    {
        if (method_exists($this->sitemapFactory, '__invoke')) {
            $this->siteMap = $this->sitemapFactory->__invoke($forceReload);
        } else {
            $this->createSiteMap($forceReload);
        }
    }

    /**
     * Set Exception application name and debug mode
     */
    protected function setExceptionParams()
    {
        org_glizy_Exception::$applicationName = __Config::get('APP_NAME');
        org_glizy_Exception::$debugMode = __Config::get('DEBUG')==true;
    }

    /**
     * @param string $command
     *
     * @return mixed
     */
    function executeCommand( $command )
    {
        $actionClass = &org_glizy_ObjectFactory::createObject( $command, null, $this );
        if ( is_object( $actionClass ) && method_exists( $actionClass, "execute" ) ) {
            $params = func_get_args();
            array_shift($params);
            return call_user_func_array( array( $actionClass, "execute" ), $params );
        }
        return null;
    }
}
