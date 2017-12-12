<?php
class movio_modules_modulesBuilder_controllers_DeleteModule extends org_glizy_mvc_core_Command
{
	function execute()
	{
		$this->checkPermissionForBackend();
		
 		if (__Request::exists('next')) {
			$pageType = $this->application->getPageType();
			list( $moduleName ) = explode( '.', $pageType );
			__Request::set( 'mbTable', $moduleName );
			$builder = org_glizy_ObjectFactory::createObject( 'movio.modules.modulesBuilder.builder.Builder' );
			$builder->executeDelete();

			org_glizy_helpers_Navigation::gotoUrl(__Link::makeUrl('link', array('pageId' => 'glizycms_contentsedit' ) ) );
		}
	}
}
