<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2011 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizycms_core_application_AdminApplication extends org_glizy_mvc_core_Application
{
    var $_pathApplicationToAdmin;
    public $hostApplicationToAdmin;

    function __construct($pathApplication='', $pathCore='', $pathApplicationToAdmin='', $configHost='')
    {
        $this->_pathApplicationToAdmin = $pathApplicationToAdmin;

        org_glizy_ObjectValues::set('org.glizy', 'admin', true);
        org_glizy_Paths::init($pathApplication, $pathCore);
        org_glizy_Paths::set('APPLICATION_MEDIA_ARCHIVE', $this->_pathApplicationToAdmin.'mediaArchive/');
        // org_glizy_Paths::set('CACHE', $this->_pathCore.'../cache/');
        // org_glizy_Paths::set('CACHE_CODE', $this->_pathCore.'../cache/');
        // org_glizy_Paths::set('CACHE_IMAGES', $this->_pathCore.'../cache/');
        // org_glizy_Paths::set('STATIC_DIR', $this->_pathCore.'../static/');
        // org_glizy_Paths::set('CORE_STATIC_DIR', $this->_pathCore.'../static/org_glizy/');
        org_glizy_Paths::add('APPLICATION_TO_ADMIN', $this->_pathApplicationToAdmin);
        org_glizy_Paths::add('APPLICATION_TO_ADMIN_CACHE', $this->_pathApplicationToAdmin.'../cache/');
        org_glizy_Paths::add('APPLICATION_TO_ADMIN_PAGETYPE', $this->_pathApplicationToAdmin.'pageTypes/');
        org_glizy_Paths::addClassSearchPath( $this->_pathApplicationToAdmin.'classes/' );

        //if (org_glizy_Config::get('SESSION_PREFIX')=='') org_glizy_Config::set('SESSION_PREFIX', 'admin');
        parent::__construct($pathApplication, $pathCore, $configHost);

        $this->addEventListener(org_glizycms_contents_events_Menu::INVALIDATE_SITEMAP, $this);
    }


    function _init()
    {
        parent::_init();

        // inizialize the editing language
        $language = org_glizy_Session::get('glizy.editingLanguageId');

        if (is_null($language))
        {
            $ar = org_glizy_ObjectFactory::createModel('org.glizycms.core.models.Language');
            $ar->language_isDefault = 1;
            $ar->language_FK_site_id = org_glizy_Config::get( 'SITE_ID' );
            $ar->find();
            org_glizy_Session::set('glizy.editingLanguage', $ar->language_code);
            org_glizy_Session::set('glizy.editingLanguageId', $ar->language_id);
            org_glizy_Session::set('glizy.editingLanguageIsDefault', $ar->language_isDefault);
            $language = $ar->language_id;
        }

        org_glizy_ObjectValues::set('org.glizy', 'editingLanguageId', $language);

        $it = org_glizy_ObjectFactory::createModelIterator('org.glizycms.core.models.Language');
        //$it = org_glizy_ObjectFactory::createModelIterator('org.glizycms.core.models.Language');

        $languagesId = array();

        foreach ($it as $ar) {
           $languagesId[] = $ar->language_id;
        }

        org_glizy_ObjectValues::set('org.glizy', 'languagesId', $languagesId);
    }

    function _startProcess()
    {
        $this->hostApplicationToAdmin = preg_replace('/\/admin$/', '', GLZ_HOST);
        parent::_startProcess();
    }

    function render_onStart()
    {
        $this->addJSLibCore();
    }

    function _loadLocale()
    {
        // importa i file di localizzazione
        if (file_exists(org_glizy_Paths::getRealPath('CORE_CLASSES').'org/glizycms/locale/'.$this->getLanguage().'.php'))
        {
            require_once(org_glizy_Paths::getRealPath('CORE_CLASSES').'org/glizycms/locale/'.$this->getLanguage().'.php');
        }
        else
        {
            require_once(org_glizy_Paths::getRealPath('CORE_CLASSES').'org/glizycms/locale/en.php');
        }
        parent::_loadLocale();
    }

    function switchEditingLanguage($id)
    {
        $ar = org_glizy_ObjectFactory::createModel('org.glizycms.core.models.Language');
        $ar->load($id);
        org_glizy_Session::set('glizy.editingLanguage', $ar->language_code);
        org_glizy_Session::set('glizy.editingLanguageId', $ar->language_id);
        org_glizy_Session::set('glizy.editingLanguageIsDefault', $ar->language_isDefault);
        org_glizy_ObjectValues::set('org.glizy', 'editingLanguageId', $ar->language_id);
    }

    function getEditingLanguageId()
    {
        return org_glizy_Session::get('glizy.editingLanguageId');
    }

    function getEditingLanguage()
    {
        return org_glizy_Session::get('glizy.editingLanguage');
    }

    function getEditingLanguageIsDefault()
    {
        return org_glizy_Session::get('glizy.editingLanguageIsDefault');
    }

    function getPathApplicationToAdmin()
    {
        return $this->_pathApplicationToAdmin;
    }

    function getLanguageId()
    {
        return 1;
    }

    function getLanguage()
    {
        return !empty($this->_user->language) ? $this->_user->language : parent::getLanguage();
    }

    function isAdmin()
    {
        return true;
    }

    public function onInvalidateSitemap()
    {
        $siteMap = org_glizy_ObjectFactory::createObject('org.glizycms.core.application.SiteMapDB');
        $siteMap->invalidate();

        $siteMap = org_glizy_ObjectFactory::createObject('org.glizy.compilers.Routing', __Paths::getRealPath('APPLICATION_TO_ADMIN_CACHE'));
        $siteMap->invalidate();
    }
}