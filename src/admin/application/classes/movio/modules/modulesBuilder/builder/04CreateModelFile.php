<?php
class movio_modules_modulesBuilder_builder_04CreateModelFile extends movio_modules_modulesBuilder_builder_AbstractCommand
{
	function execute()
	{
		$isDocument = __Request::get( 'mbModuleType', 'document' ) != 'db';
		$moduleType = $isDocument ? 'document' : 'table';
		$usePrefix = $isDocument ? 'true' : 'false';
		$tableName = $isDocument ? $this->parent->getTableName() : $this->parent->getTableNameDb();

		$fields = __Request::get( 'fieldName' );
		$types = __Request::get( 'fieldType', array() );
		$requireds = __Request::get( 'fieldRequired', array() );
		$admin = __Request::get( 'fieldAdmin', array() );
		$fieldSearch = __Request::get( 'fieldSearch', array() );
		$fieldKey = __Request::get( 'fieldKey', 'document_id' );

		$output = '<?xml version="1.0" encoding="utf-8"?>'.GLZ_COMPILER_NEWLINE2;
		$output .= '<model:Model xmlns:glz="http://www.glizy.org/dtd/1.0/" xmlns:model="http://www.glizy.org/dtd/1.0/model/"'.GLZ_COMPILER_NEWLINE2;
		$output .= 'model:tableName="'.$tableName.'" model:usePrefix="'.$usePrefix.'" model:type="'.$moduleType.'">'.GLZ_COMPILER_NEWLINE2;

		$output .= '<model:Define>'.GLZ_COMPILER_NEWLINE2;
		if ($isDocument) {
			$output .= '<model:Field name="external_id" type="int" length="255" index="true" /><model:Field name="fulltext" type="string" index="fulltext" onlyIndex="true" />'.GLZ_COMPILER_NEWLINE2;
		}

		for ( $i = 0; $i < count( $fields ); $i++ )
		{
			if ( empty( $fields[ $i ] ) ) continue;
			$required = in_array( $fields[ $i ], $requireds ) ? 'notnull' : '';
			$index = in_array( $fields[ $i ], $fieldSearch ) || in_array( $fields[ $i ], $admin ) ? 'true' : 'false';
			if ($fields[ $i ]==$fieldKey) {
				$index = 'true';
			}
			switch ( $types[ $i ] )
			{
				case 'LONG_TEXT_HTML':
				case 'LONG_TEXT':
					$output .= '<model:Field name="'.$fields[ $i ].'" type="text" validator="'.$required.'" index="'.$index.'"/>'.GLZ_COMPILER_NEWLINE2;
					break;
				case 'DATA':
					$output .= '<model:Field name="'.$fields[ $i ].'" type="date" validator="date,'.$required.'" index="'.$index.'"/>'.GLZ_COMPILER_NEWLINE2;
					break;
				case 'DATETIME':
					$output .= '<model:Field name="'.$fields[ $i ].'" type="datetime" validator="datetime,'.$required.'" index="'.$index.'"/>'.GLZ_COMPILER_NEWLINE2;
					break;
				case 'CHECKBOX':
					$output .= '<model:Field name="'.$fields[ $i ].'" type="int" length="1" index="'.$index.'"/>'.GLZ_COMPILER_NEWLINE2;
					break;
				case 'LIST':
					$output .= '<model:Field name="'.$fields[ $i ].'" type="string" length="255" validator="'.$required.'" index="true"/>'.GLZ_COMPILER_NEWLINE2;
					break;
				case 'IMAGEREPEATER':
				case 'MEDIAREPEATER':
					$output .= '<model:Field name="'.$fields[ $i ].'" type="object" readFormat="false" index="false"/>'.GLZ_COMPILER_NEWLINE2;
					break;
				case 'PICO':
					$output .= '<model:Field name="'.$fields[ $i ].'" type="text" />'.GLZ_COMPILER_NEWLINE2;
					break;
				default:
					$output .= '<model:Field name="'.$fields[ $i ].'" type="string" length="255" validator="'.$required.'" index="'.$index.'"/>'.GLZ_COMPILER_NEWLINE2;
					break;
			}
		}
		$output .= '</model:Define></model:Model>';

        $path = $this->parent->getCustomModulesFolder() . '/models';
        $file = $path . '/Model.xml';

        $r = file_put_contents( $file, $output );
        if ($r === false){
            $this->throwFileCreationException($path, $file);
        }
		return true;
	}
}
