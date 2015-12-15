<?php
class movio_modules_modulesBuilder_builder_08AddPage extends movio_modules_modulesBuilder_builder_AbstractCommand
{
	function execute()
	{
		if ( __Request::get( 'mbAddPage' ) == 1 )
		{
			// aggiunge la pagina alla struttura del sito
			$tableName = $this->parent->getTableName();
			$moduleName = $this->parent->getModuleName();
			$siteMap = &org_glizy_ObjectFactory::createObject('org.glizycms.core.application.SiteMapDB');
			$siteMap->getSiteArray();
			$menuNode = &$siteMap->getHomeNode();
			$menuProxy = org_glizy_ObjectFactory::createObject('org.glizycms.contents.models.proxy.MenuProxy');
            $pageId = $menuProxy->addMenu($moduleName, $menuNode->id, $tableName.'.views.FrontEnd');
		}
		return true;
	}
}