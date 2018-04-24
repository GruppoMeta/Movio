<?php


class movio_modules_modulesBuilder_views_components_NewTableFields extends org_glizy_components_Component
{
	function render($outputMode = NULL, $skipChilds = false)
	{
		$moduleType = __Request::get('mbModuleType');

		$output = '<table id="editTable" class="modulesBuilderTable table table-striped">';
		$output .= '<tbody>';


		$output .= '<tr id="row_0">';
		$output .= '<td width="10"><img src="application/templates/images/dragHandler.gif" /></td>';
		$output .= '<td>';
		$output .= '<input type="text" name="fieldLabel[]" value="" size="30" />';
		$output .= '</td>';
		$output .= '<td>';
		$output .= '<select name="fieldType[]">';
		$output .= '<option value="TEXT">'.__T( 'Testo' ).'</option>';
		$output .= '<option value="LONG_TEXT_HTML">'.__T( 'Testo descrittivo (html)' ).'</option>';
		$output .= '<option value="LONG_TEXT">'.__T( 'Testo lungo' ).'</option>';
		$output .= '<option value="DATA">'.__T( 'Data' ).'</option>';
		$output .= '<option value="DATETIME">'.__T( 'Data ora' ).'</option>';
		$output .= '<option value="CHECKBOX">'.__T( 'Checkbox' ).'</option>';
		$output .= '<option value="LIST">'.__T( 'Lista aperta' ).'</option>';
		$output .= '<option value="URL">'.__T( 'Link esterno' ).'</option>';
		$output .= '<option value="IMAGE">'.__T( 'Immagine' ).'</option>';
		$output .= '<option value="IMAGEURL">'.__T( 'Immagine esterna' ).'</option>';
		if ($moduleType=='document') {
			$output .= '<option value="IMAGEREPEATER"'.( $type == 'IMAGEURL' ? ' selected="selected"' : '' ).'>'.__T( 'Immagine ripetibile' ).'</option>';
		}
		$output .= '<option value="MEDIA">'.__T( 'Media' ).'</option>';
		if ($moduleType=='document') {
			$output .= '<option value="MEDIAREPEATER"'.( $type == 'IMAGEURL' ? ' selected="selected"' : '' ).'>'.__T( 'Media ripetibile' ).'</option>';
		}
		$output .= '<option value="HIDDEN">'.__T( 'Nascosto' ).'</option>';
		$output .= '</select>';
		$output .= '</td>';
		$output .= '<td style="text-align: center">';
		$output .= '<input type="checkbox" name="fieldRequired[]" value="row_0" />';
		$output .= '</td>';
		$output .= '<td style="text-align: center">';
		$output .= '<input type="checkbox" name="fieldSearch[]" value="row_0" />';
		$output .= '</td>';
		$output .= '<td style="text-align: center">';
		$output .= '<input type="checkbox" name="fieldListSearch[]" value="row_0" />';
		$output .= '</td>';
		$output .= '<td style="text-align: center">';
		$output .= '<input type="checkbox" name="fieldAdmin[]" value="row_0" />';
		$output .= '</td>';
		$output .= '<td style="text-align: center">';
		$output .= '<img class="delete" src="application/templates/images/icon_delete.gif" />';
		$output .= '</td>';
		$output .= '</tr>';

		$output .= '</tbody>';
		$output .= '<tfoot>';
		$output .= '<tr>';
		$output .= '<td colspan="8" class="newTableFoot"><a id="newTableBtnAdd" href="#">'.__T('Add field').'</a></td>';
		$output .= '</tr>';
		$output .= '</tfoot>';
		$output .= '<thead>';
		$output .= '<tr>';
		$output .= '<th></th>';
		$output .= '<th><input type="hidden" id="fieldOrder" name="fieldOrder" value="" />'.__T( 'Etichetta').'</th>';
		$output .= '<th>'.__T( 'Tipologia').'</th>';
		$output .= '<th>'.__T( 'Obbligatorio').'</th>';
		$output .= '<th>'.__T( 'Ricerca').'</th>';
		$output .= '<th>'.__T( 'Lista ricerca').'</th>';
		$output .= '<th>'.__T( 'Lista amm.').'</th>';
		$output .= '<th></th>';
		$output .= '</tr>';
		$output .= '</thead>';
		$output .= '</table>';
		$output .= '<input type="hidden" name="fieldKey" value="document_id" />';
		$output .= '<input type="hidden" name="mbModuleType" value="'.$moduleType.'" />';
		$output .= '<input type="hidden" name="mbTableDB" value="" />';
		$this->addOutputCode($output);
	}

}
