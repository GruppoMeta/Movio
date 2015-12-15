<?php
class movio_modules_ontologybuilder_controllers_graph_List extends org_glizy_mvc_core_Command
{
    public function execute()
    {
        $entityTypeId = __Request::get('entityTypeId');
        $visualization = $this->view->loadContent('visualization');

        $this->setComponentsAttribute('list', 'entityTypeId', $entityTypeId);
        $this->setComponentsAttribute('list', 'visualization', $visualization);

        // simulate a new page in site structure
        // for update the navigation menu, breadcrumbs and page title
        $entityTypeService    = org_glizy_objectFactory::createObject('movio.modules.ontologybuilder.service.EntityTypeService');
        $title          = $entityTypeService->getEntityTypeName($entityTypeId);
        $currentMenu    = $this->application->getCurrentMenu();
        $siteMap        = $this->application->getSiteMap();
        $menu = org_glizy_application_SiteMap::getEmptyMenu();
        $menu['title']      = $title;
        $menu['id']         = $currentMenu->id+100000;
        $menu['pageType']   = $currentMenu->pageType;
        $menu['url']        = __Request::get('__url__');
        $siteMap->addChildMenu($currentMenu, $menu);

        $evt = array('type' => GLZ_EVT_SITEMAP_UPDATE, 'data' => $menu['id'] );
        $this->dispatchEvent($evt);
    }
}