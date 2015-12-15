<?php
class movio_modules_modulesBuilder_builder_02SaveLocaleFiles extends movio_modules_modulesBuilder_builder_AbstractCommand
{
	function execute()
	{
		$tableName = $this->parent->getTableName();
		$language = $this->_application->getEditingLanguage();
		$fields = __Request::get( 'fieldName' );
		$labels = __Request::get( 'fieldLabel' );
		$output = '<?php'.GLZ_COMPILER_NEWLINE2;
		$output .= '$strings = array ('.GLZ_COMPILER_NEWLINE2;
		for ( $i = 0; $i < count( $fields ); $i++ )
		{
			$output .= "\t\"".$tableName.'_'.$fields[ $i ]."\" => \"".$labels[ $i ]."\",".GLZ_COMPILER_NEWLINE2;
		}
		$output .= "\t\"".$tableName."\" => \"".__Request::get( 'mbName' )."\",".GLZ_COMPILER_NEWLINE2;
		$output .= "\t\"".$tableName.".views.FrontEnd\" => \"".__Request::get( 'mbName' )."\",".GLZ_COMPILER_NEWLINE2;
		$output .= ');'.GLZ_COMPILER_NEWLINE2;
		$output .= 'org_glizy_locale_Locale::append($strings);'.GLZ_COMPILER_NEWLINE2;
		$output .= '?>';

		file_put_contents( $this->parent->getCustomModulesFolder().'/locale/'.$language.'.php', $output );
		return true;
	}
}
