<?php
class movio_modules_modulesBuilder_builder_Builder
{
	function __construct()
	{

	}

	function execute()
	{
		// crea le cartelle
		$sequence = array( '01CreateFolders', '02SaveLocaleFiles', '03CreateModule', '04CreateModelFile', '05CreateAdminPage', '06CreatePage', '07CreateRoutingFile', '08AddPage', '09AddModuleDirective' , '10AddSitemap', '11SaveModuleStructure', '12ImportCsvData' );
		foreach( $sequence as $v )
		{
			$c = org_glizy_ObjectFactory::createObject( 'movio.modules.modulesBuilder.builder.' . $v, $this );
			$r = $c->execute();
			if ( !$r )
			{
				break;
			}
		}
	}

	function executeDelete()
	{
		// crea le cartelle
		$sequence = array( 'd01DeleteFolders', 'd02CleanStartup', 'd03DeleteMenu' );
		foreach( $sequence as $v )
		{
			$c = org_glizy_ObjectFactory::createObject( 'movio.modules.modulesBuilder.builder.' . $v, $this );
			$r = $c->execute();
			if ( !$r )
			{
				break;
			}
		}
	}


	function getModuleName()
	{
		return __Request::get( 'mbName' );
	}

	function getTableName()
	{
		return __Request::get( 'mbTable' );
	}

	function getTableNameDb()
	{
		return __Request::get( 'mbTableDB' );
	}

	function getCustomModulesFolder( $fullPath = true )
	{
		return __Paths::get( 'APPLICATION_TO_ADMIN' ).'classes/userModules/'.( $fullPath ? $this->getTableName().'/' : '' );
	}

	function getPageTypeFolder()
	{
		return __Paths::get( 'APPLICATION_TO_ADMIN' ).'pageTypes/';
	}
}