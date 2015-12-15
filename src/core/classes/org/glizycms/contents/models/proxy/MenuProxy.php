<?php
class org_glizycms_contents_models_proxy_MenuProxy extends GlizyObject
{
    /**
     * Load the site map from DB
     *
     * @return org.glizycms.core.application.SiteMapDB The sitemap
     */
    public function getSiteMap($load=true)
    {
        $siteMap = org_glizy_ObjectFactory::createObject('org.glizycms.core.application.SiteMapDB');
        if ($load) {
            $siteMap->loadTree();
        }
        return $siteMap;
    }

    /**
     * Get a menu root menu record
     *
     * @return org.glizy.dataAccessDoctrine.ActiveRecord  The menu record
     */
    public function getRootMenu($languageId)
    {
        // org_glizy_dataAccessDoctrine_DataAccess::enableLogging();
        $menu = org_glizy_ObjectFactory::createModelIterator('org.glizycms.core.models.Menu')
            ->load('getRootMenu', array('languageId' => $languageId))
            ->first();

        return $menu;
    }

    /**
     * Get a menu record from id
     *
     * @return org.glizy.dataAccessDoctrine.ActiveRecord  The menu record
     */
    public function getMenuFromId($menuId, $languageId)
    {
        // org_glizy_dataAccessDoctrine_DataAccess::enableLogging();
        $menu = org_glizy_ObjectFactory::createModelIterator('org.glizycms.core.models.Menu')
            ->load('getMenu', array('menuId' => $menuId, 'languageId' => $languageId))
            ->first();

// TODO: lanciare un'eccezione se il menù non è trovato
        return $menu;
    }


    /**
     * Get child menus from id
     *
     * @return org.glizy.dataAccessDoctrine.RecordIterator
     */
    public function getChildMenusFromId($menuId, $languageId, $skipBlock=true)
    {
        // org_glizy_dataAccessDoctrine_DataAccess::enableLogging();
        $menus = org_glizy_ObjectFactory::createModelIterator('org.glizycms.core.models.Menu')
            ->load($skipBlock ? 'getChildMenusNoBlock' : 'getChildMenus', array(':menuId' => $menuId, ':languageId' => $languageId));

// TODO: lanciare un'eccezione se il menù non è trovato
        return $menus;
    }


    /**
     * Move a menu in a new position
     */
    public function moveMenu($menuId, $position, $parentId)
    {
        // load the menu from DB
        $menu = org_glizy_ObjectFactory::createModel('org.glizycms.core.models.Menu');
        $menu->load($menuId);
        // set the parent and the order
// TODO: lanciare un'eccezione se il menù non è trovato
        $menu->menu_parentId = $parentId;
        $menu->menu_order = $position;
        $menu->save();

        // reorder the children menus
        $menus = org_glizy_ObjectFactory::createModelIterator('org.glizycms.core.models.Menu');
        $menus->load('getChildrenMenuInOrder', array('menuId' => $parentId));
// TODO: lanciare un'eccezione se il parent trovato
        $pos = 0;
        foreach ($menus as $menu) {
            if ($menuId == $menu->menu_id) continue;
            if ($position == $pos) $pos++;
            $menu->menu_order = $pos;
            $menu->save();
            $pos++;
        }
        $this->invalidateSitemapCache();

        $evt = array('type' => org_glizycms_contents_events_Menu::MOVE, 'data' => $menuId);
        $this->dispatchEvent($evt);
    }

    /**
     * Delete a menu
     * @param  int $menuId the menu id
     */
    public function deleteMenu($menuId)
    {
        // cancella prima i nodi figli
        $menus = $this->getChildMenusFromId($menuId, org_glizy_ObjectValues::get('org.glizy', 'languageId'), false);
        foreach ($menus as $ar) {
            $this->deleteMenu($ar->menu_id);
        }
        $menu = org_glizy_ObjectFactory::createModel('org.glizycms.core.models.Menu');
        $menu->delete($menuId);

        $contentProxy = org_glizy_ObjectFactory::createObject('org.glizycms.contents.models.proxy.ContentProxy');
        $contentProxy->deleteContent($menuId);
        $this->invalidateSitemapCache();

        $evt = array('type' => org_glizycms_contents_events_Menu::DELETE, 'data' => $menuId);
        $this->dispatchEvent($evt);
    }

    /**
     * Add a new menu
     *
     * @param string $title    the menu title
     * @param int $parent      the menu parent id
     * @param string $pageType the menu pageType
     * @return int             new menu id
     */
    public function addMenu($title, $parent, $pageType, $type='PAGE') {
        $userId = org_glizy_ObjectValues::get('org.glizy', 'userId');

        $menus = org_glizy_ObjectFactory::createModelIterator('org.glizycms.core.models.Menu');
        $menus->load('getChildrenMenuInOrder', array('params' => array('menuId' => $parent)));
        $order = $menus->count()+1;

        $application = org_glizy_ObjectValues::get('org.glizy', 'application');
		$user = $application->getCurrentUser();

        // add the menu
        $ar = org_glizy_ObjectFactory::createModel('org.glizycms.core.models.Menu');
        $ar->menu_parentId  = $parent;
        $ar->menu_pageType  = $pageType;
        $ar->menu_order     = $order;
        $ar->menu_type      = $type;
        $ar->menu_hasPreview= '1';
        $ar->menu_url       = '';
        $ar->menu_creationDate = new org_glizy_types_DateTime();
        $ar->menu_modificationDate = new org_glizy_types_DateTime();

        $ar->menudetail_title = $title;
        $ar->menudetail_isVisible = $user->acl(__Config::get('SITEMAP_ID'), 'publish') === true ? 1 : 0;

        $pageId = $ar->save();

        // reorder all brothers menus
        $this->moveMenu($pageId, $order, $parent);

        /*
        // add the details for each languages
        $languages = org_glizy_ObjectFactory::createModelIterator('org.glizycms.core.models.Language');
        $languages->select('*')->orderBy('language_order');
        foreach ($languages as $ar) {
            $ar2 = org_glizy_ObjectFactory::createModel('org.glizycms.core.models.MenuDetail');
            $ar2->menudetail_FK_menu_id = $pageId;
            $ar2->menudetail_FK_language_id = $ar->language_id;
            $ar2->menudetail_title = $title;

// TODO dublin core
            // if ( !empty( $values['pageParent'] ) )
            // {
            //     $arParent = org_glizy_ObjectFactory::createModel('org.glizy.models.MenuDetail');
            //     $arParent->modifyField('menudetail_FK_language_id', 'defaultSelectValue', NULL);
            //     $result = $arParent->find(array('menudetail_FK_menu_id' => $values['pageParent'], 'menudetail_FK_language_id' => $this->_application->getEditingLanguageId()));

            //     $ar2->menudetail_keywords = $arParent->menudetail_keywords;
            //     $ar2->menudetail_description = $arParent->menudetail_description;
            //     $ar2->menudetail_subject = $arParent->menudetail_subject;
            //     $ar2->menudetail_creator = $arParent->menudetail_creator;
            //     $ar2->menudetail_publisher = $arParent->menudetail_publisher;
            //     $ar2->menudetail_contributor = $arParent->menudetail_contributor;
            //     $ar2->menudetail_type = $arParent->menudetail_type;
            //     $ar2->menudetail_identifier = $arParent->menudetail_identifier;
            //     $ar2->menudetail_source = $arParent->menudetail_source;
            //     $ar2->menudetail_relation = $arParent->menudetail_relation;
            //     $ar2->menudetail_coverage = $arParent->menudetail_coverage;
            // }
            // else
            // {
            //     $ar2->menudetail_keywords = '';
            //     $ar2->menudetail_description = '';
            //     $ar2->menudetail_subject = '';
            //     $ar2->menudetail_creator = '';
            //     $ar2->menudetail_publisher = '';
            //     $ar2->menudetail_contributor = '';
            //     $ar2->menudetail_type = '';
            //     $ar2->menudetail_identifier = '';
            //     $ar2->menudetail_source = '';
            //     $ar2->menudetail_relation = '';
            //     $ar2->menudetail_coverage = '';
            // }

// TODO acl associare a publish su sitemap
            $ar2->menudetail_isVisible = 1; //$user->acl('SiteMap','visible')===true ? '1' : '0';
            $ar2->save();
        }
        */

        // if ( __Config::get( 'ACL_ENABLED' ) )
        // {
        //     __Session::remove( 'glizy.aclBack' );
        //     __Session::remove( 'glizy.aclFront' );
        // }

        $this->invalidateSitemapCache();
        $evt = array('type' => org_glizycms_contents_events_Menu::ADD, 'data' => $pageId);
        $this->dispatchEvent($evt);

        return $pageId;
    }

    public function showHide($menuId, $languageId, $isShown)
    {
        $menus = $this->getChildMenusFromId($menuId, org_glizy_ObjectValues::get('org.glizy', 'languageId'), false);
        foreach ($menus as $ar) {
            $this->showHide($ar->menu_id, $languageId, $isShown);
        }

        $ar = org_glizy_ObjectFactory::createModel('org.glizycms.core.models.MenuDetail');
        if ($ar->find( array(
                'menudetail_FK_menu_id' => $menuId,
                'menudetail_FK_language_id' => $languageId ))) {

            $ar->menudetail_isVisible = $isShown ? 1 : 0;
            $ar->save();
            $this->invalidateSitemapCache();
        }
// TODO controlare che il menù esiste
    }


    public function lockUnlock($menuId, $state) {
         $ar = org_glizy_ObjectFactory::createModel('org.glizycms.core.models.Menu');
         if ($ar->load($menuId)) {
            $ar->menu_isLocked = $state ? 1 : 0;
            $ar->save();
            $this->invalidateSitemapCache();
         }
// TODO controlare che il menù esiste
    }


    public function touch($menuId) {
         $ar = org_glizy_ObjectFactory::createModel('org.glizycms.core.models.Menu');
         if ($ar->load($menuId)) {
            $ar->menu_modificationDate = new org_glizy_types_DateTime();
            $ar->save();
         }
    }

    public function rename($menuId, $languageId, $title) {
         $ar = org_glizy_ObjectFactory::createModel('org.glizycms.core.models.MenuDetail');
         if ($ar->find( array(
                'menudetail_FK_menu_id' => $menuId,
                'menudetail_FK_language_id' => $languageId ))) {

            $ar->menudetail_title = $title;
            $ar->save();
            $this->invalidateSitemapCache();

            $evt = array('type' => org_glizycms_contents_events_Menu::RENAME, 'data' => $pageId);
            $this->dispatchEvent($evt);
         }
// TODO controlare che il menù esiste
    }

    public function invalidateSitemapCache()
    {
        $evt = array('type' => org_glizycms_contents_events_Menu::INVALIDATE_SITEMAP);
        $this->dispatchEvent($evt);
    }
}