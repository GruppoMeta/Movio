<?php
class org_glizycms_contents_controllers_siteTree_ajax_GetSiteTree extends org_glizy_mvc_core_CommandAjax
{
    private $pageTypes;

    public function execute($id) {
        $this->directOutput = true;
        $output = array();

        if ($this->user->acl('glizycms', 'page.modify.pagetype')) {
            $pageTypeService = org_glizy_ObjectFactory::createObject('org.glizycms.contents.services.PageTypeService');
            $this->pageTypes = $pageTypeService->getAllPageTypes();
        }

        $languageId = org_glizy_ObjectValues::get('org.glizy', 'editingLanguageId');
        $menuProxy = org_glizy_ObjectFactory::createObject('org.glizycms.contents.models.proxy.MenuProxy');

        if ($id==0) {
            // root
            $menu = $menuProxy->getRootMenu($languageId);
            $output[] = $this->addNode($menu, true);
            return $output;
        }

        $itMenus = $menuProxy->getChildMenusFromId($id, $languageId);
        foreach($itMenus as $subMenu) {
            $output[] = $this->addNode($subMenu);
        }

        return $output;
    }

    private function addNode($menu, $isRoot=false) {
        $title = strip_tags($menu->menudetail_title);
        $icon = 'page';
        if ( $menu->menu_pageType=='Empty') {
            $icon = 'folder';
        } else if ( $menu->menu_pageType=='Alias') {
            $icon = 'alias';
        }

        if ( $menu->menu_type == 'HOMEPAGE' ) {
            $icon = 'home';
        } else if ( $menu->menu_type == 'SYSTEM' ) {
            $icon .= ' system';
        }

        if ( $menu->menu_isLocked == 1 ) {
            $icon .= ' lock';
        }

        $node = array(
            'data' => array(
                    'title' => $title,
                    'icon' => $icon
            ),
            'attr' => array(
                    'id' => $menu->menu_id,
                    'rel' => 'default',
                    'class' => '',
                    'title' => $title.' ('.$menu->menu_pageType.':'.$menu->menu_id.')',
            ),
            'metadata' => array(),
            'state' =>  ''
        );


        if ( !$menu->menudetail_isVisible ) {
            $node['data']['icon'] .= ' hide';
            $node['attr']['class'] .= ' glizycmsSiteTree-nodeHide';
        }
        if ($isRoot || $menu->numChild) {
            $node['attr']['rel'] = 'folder';
            $node['state'] = 'closed';
        }

        // stato delle varie azioni da gestire con acl
        $node['metadata']['edit'] = $this->user->acl($this->application->getPageId(),'edit');
        $node['metadata']['draft'] = 0;
        $node['metadata']['show'] = $this->user->acl($this->application->getPageId(),'visible');
        $node['metadata']['delete'] = $this->user->acl($this->application->getPageId(),'delete');
        $node['metadata']['preview'] = 0;
        $node['metadata']['publish'] = $this->user->acl($this->application->getPageId(),'publish');
        $node['metadata']['lock'] = $this->user->acl($this->application->getPageId(),'lock');
        $node['metadata']['move'] = $this->user->acl($this->application->getPageId(),'move');
        $node['metadata']['add'] = true;

        // stato della pagina
        $node['metadata']['isDraft'] = 0; //!$menu->menu_isPublished ? 1 : 0;
        $node['metadata']['isShown'] = $menu->menudetail_isVisible ? 1 : 0;
        $node['metadata']['isLocked'] = $menu->menu_isLocked ? 1 : 0;
        $node['metadata']['hasPreview'] = $menu->menu_hasPreview ? 1 : 0;
        $node['metadata']['pageType'] = $menu->menu_pageType;

        // rimuove alcune azioni se la pagina non è di tipo PAGE
        if ( $menu->menu_type != 'PAGE' ) {
            $node['metadata']['delete'] = false;
            $node['metadata']['show'] = false;
        }

        if ($this->pageTypes && $this->pageTypes[$menu->menu_pageType]) {
            $node['metadata']['pagetype'] = $menu->menu_pageType;
            $node['metadata']['acceptparent'] = $this->pageTypes[$menu->menu_pageType]['acceptParent'];
            $found = false;
            foreach($this->pageTypes as $k=>$v) {
                $found = strpos($v['acceptParent'], $menu->menu_pageType)!==false;
                if ($found) break;
            }
            $node['metadata']['add'] = $found;
        }

        return $node;
    }

}