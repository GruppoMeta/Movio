<?php
class movio_modules_modulesBuilder_AdminModuleStateController extends org_glizy_components_StateSwitchClass
{
	function executeLater_deleteModule( $oldState )
	{
		if ( strtolower( __Request::get( 'action', '' ) ) == 'next'  )
		{
			$pageType = $this->_parent->_application->getPageType();
			list( $moduleName ) = explode( '.', $pageType );
			__Session::set( 'mbTable', $moduleName );
			glz_import( 'movio.modules.modulesBuilder.builder.*', array( 'Builder.php', 'AbstractCommand.php' ) );
			$builder = org_glizy_ObjectFactory::createObject( 'movio.modules.modulesBuilder.builder.Builder' );
			$builder->executeDelete();

			glz_import( 'org.glizy.helpers.Navigation' );
			org_glizy_helpers_Navigation::gotoUrl(__Link::makeUrl('link', array('pageId' => 'SiteMap' ) ) );
		}
	}
}


