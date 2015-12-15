<?php
class movio_modules_modulesBuilder_builder_d02CleanStartup extends movio_modules_modulesBuilder_builder_AbstractCommand
{
	function execute()
	{
		$tableName = $this->parent->getTableName();
		$sitemapCustom = __Paths::get( 'APPLICATION_TO_ADMIN' ).'startup/modules_custom.php';
		if ( file_exists( $sitemapCustom ) )
		{
			$output = file_get_contents( $sitemapCustom );
			$output = preg_replace( "/\/\/\sstart\s".$tableName."\/\/([^\/])*\/\/\send\s".$tableName."\/\//i", "", $output );
			$r = file_put_contents( $sitemapCustom, $output );
		}

		return true;
	}
}
