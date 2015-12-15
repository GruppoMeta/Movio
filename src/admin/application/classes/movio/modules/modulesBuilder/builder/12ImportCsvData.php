<?php
class movio_modules_modulesBuilder_builder_12ImportCsvData extends movio_modules_modulesBuilder_builder_AbstractCommand
{
	function execute()
	{
		if (__Request::get('mbModuleType')=='csv') {
			$tableName = $this->parent->getTableName();
			$fields = __Request::get( 'fieldName' );
			$modelName = 'userModules.'.$tableName.'.models.Model';

			$fieldsMap = array();
			foreach($fields as $f) {
        		$col = str_replace('row_', '', $f);
        		$fieldsMap[] = array($f, $col);
        	}
			$ar = org_glizy_ObjectFactory::createModel($modelName);


			$csvIterator = org_glizy_ObjectFactory::createObject('movio.modules.modulesBuilder.services.CVSImporter', __Request::get('mbCsvOptions'));
	        foreach($csvIterator as $row) {
	        	if ($f) {
		        	$ar->emptyRecord();
	 	        	foreach($fieldsMap as $f) {
		        		$ar->{$f[0]} = $row->{$f[1]};
		        	}
		        	$ar->publish();
		        }
	        }
		}
		return true;
	}

}