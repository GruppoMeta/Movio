<?php
class movio_modules_modulesBuilder_builder_d03DeleteMenu extends movio_modules_modulesBuilder_builder_AbstractCommand
{
	function execute()
	{
		$tableName = $this->parent->getTableName();
		$siteMap = &org_glizy_ObjectFactory::createObject('org.glizycms.core.application.SiteMapDB');
		$siteMap->getSiteArray();
		$menuNode = $siteMap->getMenuByPageType($tableName.'.views.FrontEnd');
		if ($menuNode) {
			$menuProxy = org_glizy_ObjectFactory::createObject('org.glizycms.contents.models.proxy.MenuProxy');
            $pageId = $menuProxy->deleteMenu($menuNode->id);
		}

		org_glizy_Modules::deleteCache();
		return true;
	}

}
