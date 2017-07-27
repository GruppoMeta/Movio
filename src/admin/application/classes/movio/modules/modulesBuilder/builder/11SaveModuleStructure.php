<?php
class movio_modules_modulesBuilder_builder_11SaveModuleStructure extends movio_modules_modulesBuilder_builder_AbstractCommand
{
	function execute()
	{
		$values = array();
		$values[ 'fieldOrder' ] = __Request::get( 'fieldOrder', '' );
		$values[ 'fieldRequired' ]  = __Request::get( 'fieldRequired', array() );
		$values[ 'fieldType' ]  = __Request::get( 'fieldType', array() );
		$values[ 'fieldSearch' ]  = __Request::get( 'fieldSearch', array() );
		$values[ 'fieldListSearch' ]  = __Request::get( 'fieldListSearch', array() );
		$values[ 'fieldAdmin' ]  = __Request::get( 'fieldAdmin', array() );
		$values[ 'fieldLabel' ]  = __Request::get( 'fieldLabel', array() );
		$values[ 'fieldKey' ]  = __Request::get( 'fieldKey', 'document_id');
		$values[ 'mbModuleType' ]  = __Request::get( 'mbModuleType', 'document' ) != 'db' ? 'document' : 'db';
		$values[ 'tableDb' ]  = $this->parent->getTableNameDb();

        $path = $this->parent->getCustomModulesFolder();
        $file = $path . 'Info';
        $output = serialize($values);

        $r = file_put_contents( $file, $output );
        if ($r === false){
            $this->throwFileCreationException($path, $file);
        }
		return true;
	}

}