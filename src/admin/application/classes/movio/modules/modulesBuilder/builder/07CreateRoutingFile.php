<?php
class movio_modules_modulesBuilder_builder_07CreateRoutingFile extends movio_modules_modulesBuilder_builder_AbstractCommand
{
	function execute()
	{
		$tableName = $this->parent->getTableName();

		$fields = __Request::get( 'fieldName' );
		$fieldListSearch = __Request::get( 'fieldListSearch', array() );
		$fieldKey = __Request::get( 'fieldKey', 'document_id' );
        if (!$fieldKey) {
            $fieldKey = 'document_id';
        }
		$title = '';
		for ( $i = 0; $i < count( $fields ); $i++ )
		{
			if (in_array( $fields[ $i ], $fieldListSearch )) {
				$title = $fields[ $i ];
				break;
			}
		}

		$output = '<?xml version="1.0" encoding="utf-8"?>'.GLZ_COMPILER_NEWLINE2;
		$output .= '<glz:Routing>'.GLZ_COMPILER_NEWLINE2;
		$output .= '<glz:Route name="'.$tableName.'" value="{pageId='.$tableName.'.views.FrontEnd}/{static=state=show}/{integer='.$fieldKey.'}/{value='.$title.'}" />'.GLZ_COMPILER_NEWLINE2;
		$output .= '</glz:Routing>'.GLZ_COMPILER_NEWLINE2;

		file_put_contents( $this->parent->getCustomModulesFolder().'/config/routing.xml', $output );
		return true;
	}
}