<?php
class movio_modules_modulesBuilder_builder_01CreateFolders extends movio_modules_modulesBuilder_builder_AbstractCommand
{
	function execute()
	{
		// crea le cartelle
		// controlla se esiste la cartella che contiene tutti i moduli custom
		if ( !file_exists( $this->parent->getCustomModulesFolder( false ) ) )
		{
			mkdir( $this->parent->getCustomModulesFolder( false ) );
		}
		@mkdir( $this->parent->getCustomModulesFolder() );
		@mkdir( $this->parent->getCustomModulesFolder().'/models' );
		@mkdir( $this->parent->getCustomModulesFolder().'/views' );
		@mkdir( $this->parent->getCustomModulesFolder().'/locale' );
		@mkdir( $this->parent->getCustomModulesFolder().'/config' );
		@mkdir( $this->parent->getCustomModulesFolder().'/images' );

		@chmod( $this->parent->getCustomModulesFolder(), 0777 );
		@chmod( $this->parent->getCustomModulesFolder().'/models', 0777 );
		@chmod( $this->parent->getCustomModulesFolder().'/views', 0777 );
		@chmod( $this->parent->getCustomModulesFolder().'/locale', 0777 );
		@chmod( $this->parent->getCustomModulesFolder().'/config', 0777 );
		@chmod( $this->parent->getCustomModulesFolder().'/images', 0777 );

		return true;
	}
}
