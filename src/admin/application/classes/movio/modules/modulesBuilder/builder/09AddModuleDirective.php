<?php
class movio_modules_modulesBuilder_builder_09AddModuleDirective extends movio_modules_modulesBuilder_builder_AbstractCommand
{
	function execute()
	{
		$tableName = $this->parent->getTableName();
		$sitemapCustom = __Paths::get( 'APPLICATION_TO_ADMIN' ).'startup/modules_custom.php';
		if ( file_exists( $sitemapCustom ) )
		{
			$output = file_get_contents( $sitemapCustom );
		}
		else
		{

			$output = <<<EOD
<?php
\$application = org_glizy_ObjectValues::get('org.glizy', 'application' );
if (\$application) {
    if (!\$application->isAdmin()) {
        __Paths::addClassSearchPath( __Paths::get( 'APPLICATION_CLASSES' ).'userModules/' );
    }
//modules_custom.php
}
EOD;
		}
		// cancella entry già presenti
		$output = preg_replace( "/\/\/\sstart\s".$tableName."\/\/([^\/])*\/\/\send\s".$tableName."\/\//i", "", $output );

		// aggiunge la nuova entry
		$output = str_replace( '//modules_custom.php', '// start '.$tableName.'//'.GLZ_COMPILER_NEWLINE2.$tableName.'_Module::registerModule();'.GLZ_COMPILER_NEWLINE2.'// end '.$tableName.'//'.GLZ_COMPILER_NEWLINE2.'//modules_custom.php', $output );

		$r = file_put_contents( $sitemapCustom, $output );
		return true;
	}
}