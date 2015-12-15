<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_application_SiteMapXML extends org_glizy_application_SiteMap
{
    var $_type = 'xml';
    var $_source = NULL;

    function __construct($source=NULL)
    {
        parent::__construct();
        $this->_source = is_null($source) ? org_glizy_Paths::getRealPath('APPLICATION', org_glizy_Config::get('SITEMAP')) : $source;
    }

    function loadTree($forceReload=false)
    {
        if ($forceReload) $this->init();

        $application = &org_glizy_ObjectValues::get('org.glizy', 'application');
        $lang = $application->getLanguage();

        $options = array(
            'cacheDir' => org_glizy_Paths::get('CACHE_CODE'),
            'lifeTime' => -1,
            'readControlType' => '',
            'fileExtension' => '.php'
        );
        $cacheObj = &org_glizy_ObjectFactory::createObject('org.glizy.cache.CacheFile', $options );
        $cacheFileName = $cacheObj->verify( $this->_source, get_class( $this ).'_'.$application->getLanguage() );

        if ( $cacheFileName === false )
        {
            $this->_processSiteMapXML( $this->_source );
            $customSource = preg_replace( '/.xml$/i', '_custom.xml', $this->_source );
            if ( file_exists( $customSource ) )
            {
                $this->_processSiteMapXML( $customSource );
            }

            $cacheObj->save( serialize( $this->_siteMapArray ), NULL, get_class( $this ).'_'.$application->getLanguage() );
            $cacheObj->getFileName();
        }
        else
        {
            $this->_siteMapArray = unserialize( file_get_contents( $cacheFileName ) );
        }

        $this->_makeChilds();
    }

    function _processSiteMapXML( $fileName, $parentId = '' )
    {
        $application = &org_glizy_ObjectValues::get('org.glizy', 'application');
        $lang = $application->getLanguage();

        $modulesState = org_glizy_Modules::getModulesState();

        $xmlString = file_get_contents( $fileName );
        if ( strpos( $xmlString, '<glz:modulesAdmin />' ) )
        {
            $modulesSiteMap = '';
            $modules = org_glizy_Modules::getModules();
            foreach( $modules as $m )
            {
                if ( $m->enabled && $m->siteMapAdmin )
                {
                    $modulesSiteMap .= $m->siteMapAdmin;
                }
            }
            $xmlString = str_replace( '<glz:modulesAdmin />', $modulesSiteMap, $xmlString );
        }

        $xml = org_glizy_ObjectFactory::createObject( 'org.glizy.parser.XML' );
        $xml->loadXmlAndParseNS( $xmlString );
        $pages = $xml->getElementsByTagName('Page');
        $total = $pages->length;
        $pagesAcl = array();

        for ($i = 0; $i < $total; $i++) {
            $currNode = $pages->item( $i );

            $nodeTitle = '';
            $this->_searchNodeDetails($currNode, $nodeTitle, $lang);

            $id = $currNode->getAttribute('id');
            if (isset($modulesState[$id]) && !$modulesState[$id]) continue;

            $menu                   = $this->getEmptyMenu();
            $menu['id']             = strtolower($id);
            $menu['parentId']       = $currNode->hasAttribute('parentId') ? strtolower($currNode->getAttribute('parentId') ) :
                                            ( $currNode->parentNode->hasAttribute('id') ? strtolower($currNode->parentNode->getAttribute('id')) : '' );
            $menu['pageType']       = $currNode->hasAttribute('pageType') ? $currNode->getAttribute('pageType') : $currNode->getAttribute('id');
            $menu['isPublished']    = 1;
            $menu['isVisible']      = $currNode->getAttribute('visible');
            $menu['cssClass']       = $currNode->getAttribute('cssClass');
            $menu['icon']           = $currNode->getAttribute('icon');
            $menu['sortChild']      = $currNode->hasAttribute('sortChild') && $currNode->getAttribute('sortChild')=='true';

            if (!$currNode->hasAttribute('visible')) {
                if ( $currNode->hasAttribute('adm:acl') || in_array($menu['id'], $pagesAcl) )
                {
                    $menu['isVisible'] = '{php:$user.acl("'.$menu['id'].'", "visible")}';
                }
                else if ( !$currNode->hasAttribute('adm:acl') && $currNode->hasAttribute('adm:aclPageTypes') )
                {
                    $temp = array();
                    $aclPages = explode(',', strtolower($currNode->getAttribute('adm:aclPageTypes')));
                    foreach($aclPages as $v) {
                        $temp[] = '$user.acl("'.$v.'", "visible")';
                    }
                    $menu['isVisible'] = '{php:('.implode(' OR ', $temp).')}';
                }
            }

            $menu['title']             = $nodeTitle;
            $menu['depth']             = 1;
            $menu['childNodes']     = array();

            // solo admin
            $menu['order']             = 0;
            $menu['hasPreview']     = 0;
            $menu['type']             = 'PAGE';
            $menu['creationDate']         = 0;
            $menu['modificationDate']     = 0;
            $menu['url']                  = str_replace('%', '&', $currNode->getAttribute('url'));
            if ( strpos( $menu['url'], '&' ) === 0 )
            {
                $menu['url'] = __Link::scriptUrl( true ).$menu['url'];
            }
            $menu['select']              = strtolower($currNode->getAttribute('select'));
            $menu['nodeObj']             = NULL;
            $menu['adm:acl']         = $currNode->hasAttribute('adm:acl') ? $currNode->getAttribute('adm:acl') : null;
            $menu['adm:aclLabel']   = $currNode->hasAttribute('adm:aclLabel') ? $currNode->getAttribute('adm:aclLabel') : null;
            $menu['adm:aclPageTypes']   = $currNode->hasAttribute('adm:aclPageTypes') ? $currNode->getAttribute('adm:aclPageTypes') : null;

            if ($menu['adm:aclPageTypes']) {
                $pagesAcl = array_merge(explode(',', strtolower($menu['adm:aclPageTypes'])), $pagesAcl);
            }

            $this->_siteMapArray[$menu["id"]] = $menu;
        }
    }

    function _searchNodeDetails(&$myNode, &$title, $language)
    {
        if ( $myNode->hasAttribute('value') )
        {
            $title = $myNode->getAttribute('value');
            if (preg_match("/\{i18n\:.*\}/i", $title))
            {
                $code = preg_replace("/\{i18n\:(.*)\}/i", "$1", $title);
                $title = org_glizy_locale_Locale::getPlain($code);
            }
            return $title;
        }

        foreach( $myNode->childNodes as $currNode )
        {
            if ( ( $currNode->nodeName=='Title' || $currNode->nodeName=='glz:Title' ) && $currNode->getAttribute('lang')==$language)
            {
                $title= $currNode->hasAttribute('value') ? trim($currNode->getAttribute('value')) : trim($currNode->getText());
                break;
            }
        }
    }
}