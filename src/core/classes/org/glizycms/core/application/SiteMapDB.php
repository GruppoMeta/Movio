<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizycms_core_application_SiteMapDB extends org_glizy_application_SiteMap
{
    var $_type = 'db';
    private $cache;

    function __construct()
    {
        parent::__construct();
        $this->cache = org_glizy_ObjectFactory::createObject('org.glizy.cache.CacheFunction',
                                                $this,
                                                __Config::get('glizycms.sitemap.cacheLife'),
                                                false,
                                                __Paths::getRealPath('APPLICATION_TO_ADMIN_CACHE'));
    }

    function loadTree($forceReload=false)
    {
        if ($forceReload) $this->init();
        $emptyMenu = serialize($this->getEmptyMenu());
        $siteId = org_glizy_ObjectValues::get('org.glizy', 'siteId');
        $application = &org_glizy_ObjectValues::get('org.glizy', 'application');
        $languageId = $application->getLanguageId();
        $speakingUrl = __Config::get('glizycms.speakingUrl') === true;
        $this->_siteMapArray = $this->cache->get(__METHOD__.$siteId.'_'.$languageId, array(), function() use ($emptyMenu, $application, $speakingUrl) {
            $siteMapArray = array();
            $isAdmin = $application->isAdmin();
            $user = $application->getCurrentUser();
            $showDraft = false;
            $showDraft = isset($_GET['draft']) && $_GET['draft'] == '1';
            $languageId = method_exists($application, 'getEditingLanguageId') ? $application->getEditingLanguageId() : $application->getLanguageId();

            // TODO
            // carica le versioni dei vari men�
            // per poter disabilitare i men� che hanno solo la versione bozza
            // $menuVersion = array();
            // $it = &org_glizy_ObjectFactory::createModelIterator('org.glizy.models.ContentVersion', 'all', array('filters' => array( 'contentversion_status' =>  'DRAFT', 'contentversion_FK_language_id' => $languageId ) ) );

            // while ( $it->hasMore() )
            // {
            //     $arC = &$it->current();
            //     $it->next();
            //     $menuVersion[ $arC->contentversion_FK_menu_id ] = false;
            // }
            // $it = &org_glizy_ObjectFactory::createModelIterator('org.glizy.models.ContentVersion', 'all', array('filters' => array( 'contentversion_status' =>  'PUBLISHED', 'contentversion_FK_language_id' => $languageId ) ) );
            // while ( $it->hasMore() )
            // {
            //     $arC = &$it->current();
            //     $it->next();
            //     $menuVersion[ $arC->contentversion_FK_menu_id ] = true;
            // }
            //
            // $isAdmin = org_glizy_ObjectValues::get('org.glizy', 'admin', false);
            // $hidePrivatePage = org_glizy_Config::get( 'HIDE_PRIVATE_PAGE', true );

            // TODO chiamare il proxy
            $menus = org_glizy_ObjectFactory::createModelIterator('org.glizycms.core.models.Menu');
            $menus->load('getAllMenu', array('params' => array( 'languageId' => $languageId)));

            foreach ($menus as $ar) {
                if (is_null($ar->menu_parentId)) continue;
                $menu                     = unserialize($emptyMenu);
                $menu['id']             = $ar->menu_id;
                $menu['parentId']       = $ar->menu_parentId;
                $menu['pageType']       = $ar->menu_pageType;
                $menu['isVisible']      = $ar->menudetail_isVisible == 1 ? true : false;
                $menu['title']          = $ar->menudetail_title; //str_replace( "\n", " ", $ar->title );
                $menu['titleLink']      = $ar->menudetail_titleLink;
                $menu['linkDescription']= $ar->menudetail_linkDescription;
                $menu['depth']          = 1;
                $menu['childNodes']     = array();
                $menu['isLocked']       = $ar->menu_isLocked == '1';
                $menu['url']            = $ar->menu_url;
                $menu['cssClass']       = $ar->menu_cssClass;
                // solo admin
                $menu['order']          = $ar->menu_order;
                $menu['type']           = $ar->menu_type;
                $menu['creationDate']   = glz_localeDate2default($ar->menu_creationDate);
                $menu['modificationDate']= glz_localeDate2default($ar->menu_modificationDate);
                $menu['hasComment']     = $ar->menu_hasComment;
                $menu['printPdf']       = $ar->menu_printPdf;

                //$menu['extendsPermissions']    = $ar->menu_extendsPermissions;
                $menu['nodeObj']        = NULL;

                if ($speakingUrl && !$menu['url'] && $ar->speakingurl_value) {
                    $menu['url'] = $ar->language_code.'/'.$ar->speakingurl_value;
                }

                $siteMapArray[$menu["id"]] = $menu;
            }
            return $siteMapArray;
        });
        $this->_makeChilds();
    }

    public function invalidate()
    {
        $application = &org_glizy_ObjectValues::get('org.glizy', 'application');
        $siteId = org_glizy_ObjectValues::get('org.glizy', 'siteId');
        $iterator = org_glizy_ObjectFactory::createModelIterator('org.glizycms.core.models.Language', 'all');
        foreach($iterator as $ar) {
            $this->cache->remove('org_glizycms_core_application_SiteMapDB::loadTree'.$siteId.'_'.$ar->language_id, array());
        }
    }

    public function resolveAlias()
    {
        $application = &org_glizy_ObjectValues::get('org.glizy', 'application');
        $speakingUrlManager = $application->retrieveProxy('org.glizycms.speakingUrl.Manager');

        $IDs = array_keys($this->_siteMapArray);
        foreach ($IDs as $key)
        {
            $menu = &$this->_siteMapArray[$key];
            if (strpos($menu['url'], 'alias:')===0) {
                $url = substr($menu['url'], 6);
                $menu['url'] = $speakingUrlManager->makeUrl($url);
            }
        }
    }

}
