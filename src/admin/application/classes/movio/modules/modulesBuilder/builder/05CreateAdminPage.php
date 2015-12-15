<?php
class movio_modules_modulesBuilder_builder_05CreateAdminPage extends movio_modules_modulesBuilder_builder_AbstractCommand
{
	function execute()
	{
        $isDocument = __Request::get( 'mbModuleType', 'document' ) != 'db';
		$tableName = $this->parent->getTableName();
		$fields = __Request::get( 'fieldName' );
		$types = __Request::get( 'fieldType', array() );
		$admin = __Request::get( 'fieldAdmin', array() );
        $requireds = __Request::get( 'fieldRequired', array() );
		$fieldKey = __Request::get( 'fieldKey');
        if (!$fieldKey) {
            $fieldKey = 'document_id';
        }
        $renderClass = $isDocument ? 'org.glizycms.contents.views.renderer.CellEditDeleteVisible' : 'org.glizycms.contents.views.renderer.CellEditDelete';
		$modelName = $tableName.'.models.Model';
        $controllerName = $isDocument ? 'moduleEdit' : 'activeRecordEdit';

		$datagridFields = '';
 		for ( $i = 0; $i < count( $fields ); $i++ ) {
			if ( $types[ $i ] == IMAGEURL ||
				$types[ $i ] == IMAGE ||
				$types[ $i ] == MEDIA ) continue;

			if ( in_array( $fields[ $i ], $admin ) ) {
				$datagridFields .= '<glz:DataGridColumn columnName="'.$fields[ $i ].'" headerText="{i18n:'.$tableName.'_'.$fields[ $i ].'}" />'.GLZ_COMPILER_NEWLINE2;
			}
		}

		$formFields = '';
		for ( $i = 0; $i < count( $fields ); $i++ ) {
			if ( empty( $fields[ $i ] ) ) continue;
			$required = in_array( $fields[ $i ], $requireds ) ? 'true' : 'false';
			$label = '{i18n:'.$tableName.'_'.$fields[ $i ].'}';
			switch ( $types[ $i ] )
			{
				case 'TEXT':
				case 'IMAGEURL':
				case 'URL':
					$formFields .= '<glz:Input id="'.$fields[ $i ].'" label="'.$label.'" size="90" required="'.$required.'"/>'.GLZ_COMPILER_NEWLINE2;
					break;
				case 'LONG_TEXT_HTML':
					$formFields .= '<glz:Input id="'.$fields[ $i ].'" label="'.$label.'" size="90" required="'.$required.'" type="multiline" rows="10" cols="70" htmlEditor="true" data="type=tinymce"/>'.GLZ_COMPILER_NEWLINE2;
					break;
				case 'LONG_TEXT':
					$formFields .= '<glz:Input id="'.$fields[ $i ].'" label="'.$label.'" size="90" required="'.$required.'" type="multiline" rows="10" cols="70" />'.GLZ_COMPILER_NEWLINE2;
					break;
				case 'DATA':
				 	$formFields .= '<glz:Input id="'.$fields[ $i ].'" label="'.$label.'" size="40" required="'.$required.'" data="type=date"/>'.GLZ_COMPILER_NEWLINE2;
					break;
                 case 'DATETIME':
                    $formFields .= '<glz:Input id="'.$fields[ $i ].'" label="'.$label.'" size="40" required="'.$required.'" data="type=datetime"/>'.GLZ_COMPILER_NEWLINE2;
                    break;
                case 'CHECKBOX':
                    $formFields .= '<glz:Checkbox id="'.$fields[ $i ].'" label="'.$label.'" required="'.$required.'" data="type=checkbox"/>'.GLZ_COMPILER_NEWLINE2;
                    break;
				case 'LIST':
				 	$formFields .= '<glz:Input id="'.$fields[ $i ].'" label="'.$label.'" size="90" required="'.$required.'" data="type=selectfrom;multiple=false;add_new_values=true;model='.$modelName.'"/>'.GLZ_COMPILER_NEWLINE2;
					break;
				case 'IMAGE':
 					$formFields .= '<glz:Input id="'.$fields[ $i ].'" label="'.$label.'" size="90" required="'.$required.'" data="type=mediapicker;mediatype=IMAGE;preview=true"/>'.GLZ_COMPILER_NEWLINE2;
					break;
				case 'MEDIA':
					$formFields .= '<glz:Input id="'.$fields[ $i ].'" label="'.$label.'" size="90" required="'.$required.'" data="type=mediapicker;mediatype=ALL;preview=false"/>'.GLZ_COMPILER_NEWLINE2;
					break;
                case 'IMAGEREPEATER':
                    $formFields .= '<glz:Fieldset id="'.$fields[ $i ].'" label="'.$label.'" data="type=repeat;collapsable=false">'.GLZ_COMPILER_NEWLINE2;
                    $formFields .= '<glz:Input id="image_id" label="{i18n:Immagine}" size="90" data="type=mediapicker;mediatype=IMAGE;preview=true"/>'.GLZ_COMPILER_NEWLINE2;
                    $formFields .= '</glz:Fieldset>'.GLZ_COMPILER_NEWLINE2;
                    break;
                case 'MEDIAREPEATER':
                    $formFields .= '<glz:Fieldset id="'.$fields[ $i ].'" label="'.$label.'" data="type=repeat;collapsable=false">'.GLZ_COMPILER_NEWLINE2;
                    $formFields .= '<glz:Input id="media_id" label="{i18n:Media}" size="90" data="type=mediapicker;mediatype=ALL;preview=false"/>'.GLZ_COMPILER_NEWLINE2;
                    $formFields .= '</glz:Fieldset>'.GLZ_COMPILER_NEWLINE2;
                    break;
				case 'HIDDEN':
					$formFields .= '<glz:Hidden id="'.$fields[ $i ].'" />'.GLZ_COMPILER_NEWLINE2;
					break;
			}
		}
$output = <<<EOD
<?xml version="1.0" encoding="utf-8"?>
<glz:include
    xmlns:cms="org.glizycms.views.components.*"
    xmlns:mvc="org.glizy.mvc.components.*"
    src="movio.views.TemplateModuleAdmin">

    <glz:template name="model" value="$modelName" />
    <glz:template name="primary_key" value="$fieldKey" />
    <glz:template name="controller_name" value="org.glizycms.contents.controllers.{$controllerName}.*" />
    <glz:template name="render_class" value="$renderClass" />


    <glz:template name="grid_fields">
        $datagridFields
    </glz:template>

    <glz:template name="form_fields">
        $formFields
    </glz:template>

    <glz:template name="custom_states">
        <mvc:State name="deleteModule">
            <glz:LongText><![CDATA[ Are you sure you want to delete the module? <br /> The files of the module and the site page will be deleted]]></glz:LongText>
            <glz:Form id="myForm" removeGetValues="false" controllerName="movio.modules.modulesBuilder.controllers.DeleteModule">
                <cms:FormButtonsPanel>
                    <glz:HtmlButton label="{i18n:Delete module}" id="next" value="next" cssClass="btn" />
                </cms:FormButtonsPanel>
            </glz:Form>
        </mvc:State>
    </glz:template>
</glz:include>
EOD;

		file_put_contents( $this->parent->getCustomModulesFolder().'/views/Admin.xml', $output );
		return true;
	}
}