<?php
class movio_modules_modulesBuilder_builder_06CreatePage extends movio_modules_modulesBuilder_builder_AbstractCommand
{

	function execute()
	{
		$tableName = $this->parent->getTableName();
		$fields = __Request::get( 'fieldName' );
		$types = __Request::get( 'fieldType', array() );
		$fieldSearch = __Request::get( 'fieldSearch', array() );
		$fieldListSearch = __Request::get( 'fieldListSearch', array() );
		$modelName = $tableName.'.models.Model';
		$routeUrl = $tableName;
		$fieldImagesInList = '';
		$fieldImages = '';
		$firstFieldName = '';
		$skinList = '';
		$skinEntry = '';
		$fieldKey = __Request::get( 'fieldKey');
        if (!$fieldKey) {
            $fieldKey = 'document_id';
        }

		// form di ricerca
		$fieldInSearch = '';
		for ( $i = 0; $i < count( $fields ); $i++ )
		{
			if ( in_array( $fields[ $i ], $fieldSearch ) )
			{
				switch ( $types[ $i ] )
				{
					case 'LIST':
						$fieldInSearch .= '<glz:DataDictionary id="'.$fields[ $i ].'DP" recordClassName="'.$modelName.'" field="'.$fields[ $i ].'" />'.GLZ_COMPILER_NEWLINE2;
						$fieldInSearch .= '<glz:List id="'.$fields[ $i ].'" label="{i18n:'.$tableName.'_'.$fields[ $i ].'}" dataProvider="{'.$fields[ $i ].'DP}" value="{filters}" emptyValue="{i18n:MW_EMPTY}" />'.GLZ_COMPILER_NEWLINE2;
						break;

					default:
						$fieldInSearch .= '<glz:Input id="'.$fields[ $i ].'" label="{i18n:'.$tableName.'_'.$fields[ $i ].'}" value="{filters}" />'.GLZ_COMPILER_NEWLINE2;
						break;
				}
			}
		}


		// definizione delle skin per il risultato della ricerca
		$firstImageId = '';
		$firstImageRepeater = false;
		for ( $i = 0; $i < count( $fields ); $i++ )
		{
			if( $types[ $i ] == 'IMAGEURL' || $types[ $i ] == 'IMAGE' )
			{
				$firstImageId = $fields[ $i ];
				$fieldImagesInList = '<glz:Image id="'.$firstImageId.'" width="{config:IMG_LIST_WIDTH}" height="{config:IMG_LIST_HEIGHT}" cssClass="thumb" />'.GLZ_COMPILER_NEWLINE2;
				break;
			} else if ($types[ $i ] == 'IMAGEREPEATER') {
				$firstImageRepeater = true;
				$firstImageId = $fields[ $i ];
				$fieldImagesInList = '<glz:Repeater id="'.$firstImageId.'"><glz:Image id="image_id" width="{config:IMG_LIST_WIDTH}" height="{config:IMG_LIST_HEIGHT}" cssClass="thumb" /></glz:Repeater>'.GLZ_COMPILER_NEWLINE2;
				break;
			}
		}
		$skinList .= '<section class="results-content clearfix" tal:condition="php: !is_null(Component.records)">'.GLZ_COMPILER_NEWLINE2;
		$skinList .= '<h3 tal:condition="Component/title" tal:content="structure Component/title"/>'.GLZ_COMPILER_NEWLINE2;
		$skinList .= '<span tal:omit-tag="" tal:condition="php: Component.records.count() > 0">'.GLZ_COMPILER_NEWLINE2;
		$skinList .= '<article tal:repeat="item Component/records" tal:attributes="class php: item.getFieldValue(\'__cssClass__\') . \' item clearfix\'">'.GLZ_COMPILER_NEWLINE2;

		if ( $firstImageId ) {
			if (!$firstImageRepeater) {
				$skinList .= '<figure tal:condition="php: item.'.$firstImageId.'[\'mediaId\'] gt 0">'.GLZ_COMPILER_NEWLINE2;
				$skinList .= '<a href="" tal:attributes="href item/__url__; title item/##firstFieldName##"><span tal:omit-tag="" tal:content="structure item/'.$firstImageId.'/__html__" /></a>'.GLZ_COMPILER_NEWLINE2;
				$skinList .= '</figure>'.GLZ_COMPILER_NEWLINE2;
			} else {
				$skinList .= '<figure tal:condition="php: count(item.'.$firstImageId.')>0">'.GLZ_COMPILER_NEWLINE2;
				$skinList .= '<span  tal:omit-tag="" tal:define="image php: item.'.$firstImageId.'[0].image_id">'.GLZ_COMPILER_NEWLINE2;
				$skinList .= '<a href="" tal:attributes="href item/__url__; title item/##firstFieldName##"><span tal:omit-tag="" tal:content="structure image/__html__" /></a>'.GLZ_COMPILER_NEWLINE2;
				$skinList .= '</span>'.GLZ_COMPILER_NEWLINE2;
				$skinList .= '</figure>'.GLZ_COMPILER_NEWLINE2;
			}
		}

		$first = false;
		for ( $i = 0; $i < count( $fields ); $i++ )
		{
			if ( in_array( $fields[ $i ], $fieldListSearch ) )
			{
				if ( $types[ $i ] == 'HIDDEN' ||
					$types[ $i ] == 'IMAGEURL' ||
					$types[ $i ] == 'IMAGE' ||
					$types[ $i ] == 'IMAGEREPEATER' ||
					$types[ $i ] == 'MEDIAREPEATER' ||
					$types[ $i ] == 'MEDIA' ) continue;

				if ( !$first )
				{
					// primo campo, viene usato come titolo
					$first = true;
					$firstFieldName = $fields[ $i ];
					$skinList .= '<h1><a tal:attributes="href item/__url__; title item/'.$fields[ $i ].'" tal:content="structure item/'.$fields[ $i ].'"></a></h1>'.GLZ_COMPILER_NEWLINE2;
				}
				else
				{
					$skinList .= '<p tal:condition="item/'.$fields[ $i ].'"><strong tal:content="structure php: __T(\''.$tableName.'_'.$fields[ $i ].'\')"></strong>:&nbsp;<span tal:omit-tag="" tal:content="structure item/'.$fields[ $i ].'" /></p>'.GLZ_COMPILER_NEWLINE2;
				}
			}
		}
		$skinList .= '</article>'.GLZ_COMPILER_NEWLINE2;
		$skinList .= '</span>'.GLZ_COMPILER_NEWLINE2;
		$skinList .= '<span tal:omit-tag="" tal:condition="php: Component.records.count() == 0">'.GLZ_COMPILER_NEWLINE2;
		$skinList .= '<article class="item clearfix">'.GLZ_COMPILER_NEWLINE2;
		$skinList .= '<p tal:content="php:__T(\'MW_NO_RECORD_FOUND\')"></p>'.GLZ_COMPILER_NEWLINE2;
		$skinList .= '</article>'.GLZ_COMPILER_NEWLINE2;
		$skinList .= '</span>'.GLZ_COMPILER_NEWLINE2;
		$skinList .= '</section>'.GLZ_COMPILER_NEWLINE2;
		$skinList = str_replace('##firstFieldName##', $firstFieldName, $skinList);

		// definizione delle skin per il dettaglio
		$first = false;
		$image = '';
		$imageList = '';
		$details = '';
		$text = '';
		$afterText = '';
		$skinEntry .= '<span tal:omit-tag="">'.GLZ_COMPILER_NEWLINE2;
		$skinEntry .= '<div class="clearfix">'.GLZ_COMPILER_NEWLINE2;
		$firstFieldName = '';
		for ( $i = 0; $i < count( $fields ); $i++ )
		{
			if ( $types[ $i ] == 'HIDDEN' ) continue;

			if ( !$first )
			{
				// primo campo, viene usato come titolo
				$first = true;
				$firstFieldName = $fields[ $i ];
				// $skinEntry .= '<h2 tal:content="structure Component/'.$fields[ $i ].'" />'.GLZ_COMPILER_NEWLINE2;
				// $skinEntry .= '<div class="clear spacer"></div>'.GLZ_COMPILER_NEWLINE2;
			}
			else
			{
				$img = '';
				switch ( $types[ $i ] )
				{
                    case 'IMAGEURL':
                        $fieldImages .= '<glz:ImageExternal id="'.$fields[ $i ].'" width="{config:IMG_WIDTH}" height="{config:IMG_HEIGHT}" zoom="true" />'.GLZ_COMPILER_NEWLINE2;
                        $img = '<figure class="main-img align-right" tal:condition="php: !empty( Component.'.$fields[ $i ].'[\'mediaUrl\'] )" tal:content="structure Component/'.$fields[ $i ].'/__html__"></figure>'.GLZ_COMPILER_NEWLINE2;
                        break;

                    case 'IMAGE':
                        $fieldImages .= '<glz:Image id="'.$fields[ $i ].'" width="{config:IMG_WIDTH}" height="{config:IMG_HEIGHT}" zoom="true" superZoom="true" />'.GLZ_COMPILER_NEWLINE2;
                        $img = '<figure class="main-img align-right" tal:condition="php: Component.'.$fields[ $i ].'[\'mediaId\'] gt 0" tal:content="structure Component/'.$fields[ $i ].'/__html__"></figure>'.GLZ_COMPILER_NEWLINE2;
                        break;

					case 'IMAGEREPEATER':
						$fieldImages .= '<glz:Repeater id="'.$fields[ $i ].'"><glz:Script extendParent="true"><![CDATA['.
										'function loadContent($id, $bindTo="") {'.
										'if ($this->contentCount==1) {'.
										'$c = $this->getComponentById($this->getId()."-image_id");'.
										'$c->setAttributes(array("width" => __Config::get("THUMB_WIDTH"), "height" => __Config::get("THUMB_HEIGHT")));'.
										'}'.
										'return parent::loadContent($id, $bindTo);'.
										'}'.
										']]></glz:Script>'.
										'<glz:Image id="image_id" width="{config:IMG_WIDTH}" height="{config:IMG_HEIGHT}" zoom="true" group="slideshow" superZoom="true"/></glz:Repeater>'.GLZ_COMPILER_NEWLINE2;
						$afterText .= '<div id="imageList" tal:condition="php: count(Component.'.$fields[ $i ].')">'.GLZ_COMPILER_NEWLINE2;
						$afterText .= '<span tal:omit-tag="" tal:repeat="item Component/'.$fields[ $i ].'">'.GLZ_COMPILER_NEWLINE2;
						$afterText .= '<span tal:omit-tag="" tal:condition="php: repeat.item.number > 1" tal:content="structure item/image_id/__html__" />'.GLZ_COMPILER_NEWLINE2;
						$afterText .= '</span>'.GLZ_COMPILER_NEWLINE2;
						$afterText .= '<div class="clear"></div></div>'.GLZ_COMPILER_NEWLINE2;
						$img = '<span tal:omit-tag="" tal:condition="php: count(Component.'.$fields[ $i ].')" tal:repeat="item Component/'.$fields[ $i ].'">'.
								'<span tal:omit-tag="" tal:condition="php: repeat.item.number == 1" tal:content="structure item/image_id/__html__" />'.
								'</span>';
						break;

					case 'MEDIAREPEATER':
						$fieldImages .= '<glz:Repeater id="'.$fields[ $i ].'"><glz:Media id="media_id" label="{i18n:MW_DOCUMENT}" linkTitle="{i18n:MW_DOWNLOAD_FILE_LINK}" /></glz:Repeater>'.GLZ_COMPILER_NEWLINE2;
						$afterText .= '<span tal:omit-tag="" tal:condition="php: count(Component.'.$fields[ $i ].')">'.GLZ_COMPILER_NEWLINE2;
						$afterText .= '<h4 tal:content="structure php: __T(\''.$tableName.'_'.$fields[ $i ].'\')" class="downloadTitle" />'.GLZ_COMPILER_NEWLINE2;
						$afterText .= '<ul class="downloadList">'.GLZ_COMPILER_NEWLINE2;
						$afterText .= '<li tal:repeat="item Component/'.$fields[ $i ].'" tal:content="structure item/media_id/__html__" />'.GLZ_COMPILER_NEWLINE2;
						$afterText .= '</ul>'.GLZ_COMPILER_NEWLINE2;
						$afterText .= '</span>'.GLZ_COMPILER_NEWLINE2;
						break;

					case 'MEDIA':
						$fieldImages .= '<glz:Media id="'.$fields[ $i ].'" label="{i18n:MW_DOCUMENT}" linkTitle="{i18n:MW_DOWNLOAD_FILE_LINK}" />'.GLZ_COMPILER_NEWLINE2;
						$details .= '<span tal:omit-tag="" tal:condition="php: Component.'.$fields[ $i ].'!=\'\'">'.GLZ_COMPILER_NEWLINE2;
						$details .= '<dt tal:content="structure php: __T(\''.$tableName.'_'.$fields[ $i ].'\')"></dt>'.GLZ_COMPILER_NEWLINE2;
						$details .= '<dd tal:omit-tag="" tal:content="structure Component/'.$fields[ $i ].'/__html__"></dd>'.GLZ_COMPILER_NEWLINE2;
						$details .= '</span>'.GLZ_COMPILER_NEWLINE2;
						break;

					case 'TEXT':
					case 'DATA':
					case 'LIST':
					case 'URL':
						if ($types[ $i ]=='URL') {
							$fieldImages .= '<glz:LinkTo id="'.$fields[ $i ].'" label="{i18n:Link esterno}" />'.GLZ_COMPILER_NEWLINE2;
						}

						$details .= '<span tal:omit-tag="" tal:condition="php: Component.'.$fields[ $i ].'!=\'\'">'.GLZ_COMPILER_NEWLINE2;
						$details .= '<dt tal:content="structure php: __T(\''.$tableName.'_'.$fields[ $i ].'\')"></dt>'.GLZ_COMPILER_NEWLINE2;
						$details .= '<dd tal:content="structure Component/'.$fields[ $i ].'"></dd>'.GLZ_COMPILER_NEWLINE2;
						$details .= '</span>'.GLZ_COMPILER_NEWLINE2;
						break;

					case 'LONG_TEXT_HTML':
					case 'LONG_TEXT':
						$text .= '<span tal:omit-tag="" tal:condition="php: !glz_empty(Component.'.$fields[ $i ].')">'.GLZ_COMPILER_NEWLINE2;
						$text .= '<h3 tal:content="structure php: __T(\''.$tableName.'_'.$fields[ $i ].'\')" />'.GLZ_COMPILER_NEWLINE2;
						$text .= '<span tal:omit-tag="" tal:content="structure Component/'.$fields[ $i ].'" />'.GLZ_COMPILER_NEWLINE2;
						$text .= '</span>'.GLZ_COMPILER_NEWLINE2;
						break;

					case 'HIDDEN':
						break;
				}

				if ( !empty( $img ) )
				{
					if ( empty( $image ) )
					{
						$image = $img;
					}
					else
					{
						$imageList .= $img;
					}
				}
			}
		}

		$skinEntry .= !empty( $image ) ? $image : '';
		$skinEntry .= !empty( $details ) ? '<dl class="properties">'.$details.'</dl>' : '';
		$skinEntry .= '<div class="clear"></div>'.$text.$afterText;
		$skinEntry .= '</div>'.GLZ_COMPILER_NEWLINE2;
		if ( !empty( $imageList ) )
		{
			$skinEntry .= '<div id="imageList">'.$imageList.'</div>'.GLZ_COMPILER_NEWLINE2;
		}
		$skinEntry .= '</span>'.GLZ_COMPILER_NEWLINE2;


		$output = <<<EOD
<?xml version="1.0" encoding="iso-8859-1"?>
<glz:Page id="Page"
	xmlns:glz="http://www.glizy.org/dtd/1.0/"
	xmlns:cms="org.glizycms.views.components.*"
	templateType="php"
	templateFileName="page.php"
	defaultEditableRegion="content"
	adm:editComponents="filters, text">
	<glz:Import src="Common.xml" />
	<glz:DataProvider id="ModuleDP" recordClassName="$modelName" order="$firstFieldName" />
	<glz:StateSwitch defaultState="list" rememberState="false">
		<glz:State name="list">
			<glz:LongText id="text" label="{i18n:MW_PARAGRAPH_TEXT}" forceP="true" adm:rows="20" adm:cols="75" adm:htmlEditor="true" />
			<cms:SearchFilters id="filters" cssClass="search my-form clearfix" label="{i18n:Show search form}">
				$fieldInSearch
				<glz:Panel cssClass="control-group">
					<glz:HtmlButton label="{i18n:MW_SEARCH}" value="SEARCH" target="{filters}" />
					<glz:HtmlButton label="{i18n:MW_NEW_SEARCH}" value="RESET" target="{filters}" cssClass="reset" />
				</glz:Panel>
			</cms:SearchFilters>
			<glz:RecordSetList id="list" dataProvider="{ModuleDP}" routeUrl="$routeUrl" title="{i18n:MW_SEARCH_RESULT}" filters="{filters}" paginate="{paginate}" removeTitleWithNoFilter="true" skin="{listSkin}">
			$fieldImagesInList
			</glz:RecordSetList>
            <glz:PaginateResult id="paginate" cssClass="pagination" />
		</glz:State>
		<glz:State name="show">
            <glz:RecordDetail id="entry" dataProvider="{ModuleDP}" idName="$fieldKey" skin="{entrySkin}" ogTitle="$firstFieldName">
                $fieldImages
            </glz:RecordDetail>
            <glz:Link id="backbtn" editableRegion="afterContent" cssClass="moreLeft" label="{i18n:MW_BACK_TO_SEARCH}" />
		</glz:State>
	</glz:StateSwitch>
	<glz:SkinDefine id="listSkin"><![CDATA[
$skinList
]]></glz:SkinDefine>
	<glz:SkinDefine id="entrySkin"><![CDATA[
$skinEntry
]]></glz:SkinDefine>
</glz:Page>
EOD;

        $path = $this->parent->getCustomModulesFolder() . '/views';
        $file = $path . '/FrontEnd.xml';

        $r = file_put_contents( $file, $output );
        if ($r === false){
            $this->throwFileCreationException($path, $file);
        }
        return true;
	}
}