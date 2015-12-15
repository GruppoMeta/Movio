<?php
class movio_modules_modulesBuilder_views_components_EditTableFields extends org_glizy_components_Component
{
	function render()
	{
		$tableName = __Request::get( 'mbTableDB' );
		$moduleType = __Request::get('mbModuleType');
		$fieldOrder = __Request::get( 'fieldOrder', array());
		$fieldRequired = __Request::get( 'fieldRequired', array() );
		$fieldType = __Request::get( 'fieldType', array() );
		$fieldSearch = __Request::get( 'fieldSearch', array() );
		$fieldListSearch = __Request::get( 'fieldListSearch', array() );
		$fieldAdmin = __Request::get( 'fieldAdmin', array() );
		$fieldLabel = __Request::get( 'fieldLabel', array() );
		$fieldPrimaryKey = '';

		if ($moduleType=='document') {
			$fieldOrder =  explode(',', $fieldOrder);
		} else if ($moduleType=='csv') {
			$fieldOrder = array();
			$fieldType = array();
			$csvIterator = org_glizy_ObjectFactory::createObject('movio.modules.modulesBuilder.services.CVSImporter', __Request::get('mbCsvOptions'));
	        $fieldLabel = $csvIterator->getHeading();
	        $i = 0;
	        foreach($fieldLabel as $f) {
	        	if (!$f) continue;
	        	$fieldOrder[] = 'row_'.$i++;
	        	$fieldType[] = 'TEXT';
	        }
		} else {

			$connection = org_glizy_dataAccessDoctrine_DataAccess::getConnection();
	        $sm = new org_glizy_dataAccessDoctrine_SchemaManager($connection);
	        $fields = $sm->getFields($tableName);

			$fieldOrder = array();
			$fieldType = array();
			$fieldLabel = array();
			foreach($fields as $f) {
				$fieldLabel[] = $f->name;
				$fieldOrder[] = $f->name;
				if ($f->key) {
					$fieldPrimaryKey = $f->name;
				}

				switch ( $f->type ) {
					case 'text':
						$type = 'longtext';
						break;
					case 'date':
					case 'datetime':
						$type = 'date';
						break;
					default:
						$type = 'text';
						break;
				}
				$fieldType[] = $type;
			}

		}

		$output = '<table id="editTable" class="modulesBuilderTable table table-striped">';
		$output .= '<tbody>';
		$theadFields = '';

		for ($i = 0; $i < count( $fieldOrder); $i++ )
		{
			$type =  $fieldType[$i];
			$label = $fieldLabel[$i];
			$id = $fieldOrder[$i];

			$output .= '<tr id="'.$id.'">';
			$output .= '<td width="10"><img src="application/templates/images/dragHandler.gif" /></td>';
			$output .= '<td>';
			$output .= '<input type="text" name="fieldLabel[]" value="'.$label.'" size="30" />';
			$output .= '</td>';
			$output .= '<td>';
			$output .= '<select name="fieldType[]">';
			$output .= '<option value="TEXT"'.( $type == 'TEXT' ? ' selected="selected"' : '' ).'>'.__T( 'Testo' ).'</option>';
			$output .= '<option value="LONG_TEXT_HTML"'.( $type == 'LONG_TEXT_HTML' ? ' selected="selected"' : '' ).'>'.__T( 'Testo descrittivo (html)' ).'</option>';
			$output .= '<option value="LONG_TEXT"'.( $type == 'LONG_TEXT' ? ' selected="selected"' : '' ).'>'.__T( 'Testo lungo' ).'</option>';
			$output .= '<option value="DATA"'.( $type == 'DATA' ? ' selected="selected"' : '' ).'>'.__T( 'Data' ).'</option>';
			$output .= '<option value="DATETIME"'.( $type == 'DATETIME' ? ' selected="selected"' : '' ).'>'.__T( 'Data ora' ).'</option>';
			$output .= '<option value="CHECKBOX"'.( $type == 'CHECKBOX' ? ' selected="selected"' : '' ).'>'.__T( 'Checkbox' ).'</option>';
			$output .= '<option value="LIST"'.( $type == 'LIST' ? ' selected="selected"' : '' ).'>'.__T( 'Lista aperta' ).'</option>';
			$output .= '<option value="URL"'.( $type == 'URL' ? ' selected="selected"' : '' ).'>'.__T( 'Link esterno' ).'</option>';
			$output .= '<option value="IMAGE"'.( $type == 'IMAGE' ? ' selected="selected"' : '' ).'>'.__T( 'Immagine' ).'</option>';
			$output .= '<option value="IMAGEURL"'.( $type == 'IMAGEURL' ? ' selected="selected"' : '' ).'>'.__T( 'Immagine esterna' ).'</option>';
			if ($moduleType=='document') {
				$output .= '<option value="IMAGEREPEATER"'.( $type == 'IMAGEREPEATER' ? ' selected="selected"' : '' ).'>'.__T( 'Immagine ripetibile' ).'</option>';
			}
			$output .= '<option value="MEDIA"'.( $type == 'MEDIA' ? ' selected="selected"' : '' ).'>'.__T( 'Media' ).'</option>';
			if ($moduleType=='document') {
				$output .= '<option value="MEDIAREPEATER"'.( $type == 'MEDIAREPEATER' ? ' selected="selected"' : '' ).'>'.__T( 'Media ripetibile' ).'</option>';
			}
			$output .= '<option value="HIDDEN"'.( $type == 'HIDDEN' ? ' selected="selected"' : '' ).'>'.__T( 'Nascosto' ).'</option>';
			// $output .= '<option value="PICO"'.( $type == 'PICO' ? ' selected="selected"' : '' ).'>'.__T( 'Pico thesaurus' ).'</option>';
			$output .= '</select>';
			$output .= '</td>';
			$output .= '<td style="text-align: center">';
			$output .= '<input type="checkbox" name="fieldRequired[]" value="'.$id.'" '.( in_array( $id, $fieldRequired ) ? 'checked="checked"' : '' ).'/>';
			$output .= '</td>';
			$output .= '<td style="text-align: center">';
			$output .= '<input type="checkbox" name="fieldSearch[]" value="'.$id.'" '.( in_array( $id, $fieldSearch ) ? 'checked="checked"' : '' ).'/>';
			$output .= '</td>';
			$output .= '<td style="text-align: center">';
			$output .= '<input type="checkbox" name="fieldListSearch[]" value="'.$id.'" '.( in_array( $id, $fieldListSearch ) ? 'checked="checked"' : '' ).'/>';
			$output .= '</td>';
			$output .= '<td style="text-align: center">';
			$output .= '<input type="checkbox" name="fieldAdmin[]" value="'.$id.'" '.( in_array( $id, $fieldAdmin ) ? 'checked="checked"' : '' ).'/>';
			$output .= '</td>';
			$output .= '</tr>';
		}
		$output .= '</tbody>';
		$output .= '<thead>';
		$output .= '<tr>';
		$output .= '<th></th>';
		$output .= '<th><input type="hidden" id="fieldOrder" name="fieldOrder" value="" />';
		$output .= '<input type="hidden" id="moduleType" name="moduleType" value="'.($moduleType != 'db' ? 'document' : 'db').'" />';
		$output .= '<input type="hidden" id="fieldKey" name="fieldKey" value="'.$fieldPrimaryKey.'" />';
		$output .= $theadFields.__T( 'Etichetta').'</th>';
		$output .= '<th>'.__T( 'Tipologia').'</th>';
		$output .= '<th>'.__T( 'Obbligatorio').'</th>';
		$output .= '<th>'.__T( 'Ricerca').'</th>';
		$output .= '<th>'.__T( 'Lista ricerca').'</th>';
		$output .= '<th>'.__T( 'Lista amm.').'</th>';
		$output .= '</tr>';
		$output .= '</thead>';

		$output .= '</table>';
		$output .= '<input type="hidden" name="mbModuleType" value="'.$moduleType.'" />';
		$output .= '<input type="hidden" name="mbTableDB" value="'.$tableName.'" />';
		$this->addOutputCode($output);
	}

}