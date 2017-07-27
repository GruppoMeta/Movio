<?php
class movio_modules_modulesBuilder_builder_02SaveLocaleFiles extends movio_modules_modulesBuilder_builder_AbstractCommand
{
	function execute()
	{
		$tableName = $this->parent->getTableName();
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

        $path = $this->parent->getCustomModulesFolder() . 'locale/';
        $language = $this->_application->getEditingLanguage();
        $iterator = org_glizy_ObjectFactory::createModelIterator('org.glizycms.core.models.Language');
        foreach ($iterator as $ar) {
            $this->saveLocale($path.$ar->language_code.'.php', $output, $language==$ar->language_code);
        }

        return true;
	}

    private function saveLocale($path, $content, $forceSave)
    {
        if (!file_exists($path) || $forceSave) {
            if (file_put_contents($path, $content ) === false){
                $pathInfo = pathInfo($path);
                $this->throwFileCreationException($pathInfo['dirname'], $pathInfo['basename']);
            }
        }
    }
}
