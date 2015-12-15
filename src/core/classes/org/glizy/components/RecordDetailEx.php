<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_components_RecordDetailEx extends org_glizy_components_Component
{
	var $recordId;
	var $items = array();

	/**
	 * Init
	 *
	 * @return	void
	 * @access	public
	 */
	function init()
	{
		$this->defineAttribute('dataProvider',	true, 	NULL,	COMPONENT_TYPE_OBJECT);
		$this->defineAttribute('idName',		false, 	'id',	COMPONENT_TYPE_STRING);
		$this->defineAttribute('getRelations', 	false,	false,	COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('routeUrl', 		false,	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('query', 	false,	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('createModelComponents', 	false,	true, COMPONENT_TYPE_BOOLEAN);
		parent::init();
	}


	function process()
	{
		$this->recordId			= org_glizy_Request::get($this->getAttribute('idName'), NULL);
		if (is_null($this->recordId)) return;

		$dataProvider 		= &$this->getAttribute('dataProvider');
		$ar					= &$dataProvider->getNewObject();
		$versionFieldName 	= $ar->getVersionFieldName();
		$languageFieldName 	= $ar->getLanguageFieldName();

		if (is_null($versionFieldName))
		{
			$ar->load( $this->recordId, $this->getAttribute('query') );
			$this->_content	= $ar->getValuesAsArray( $this->getAttribute('getRelations'), true );
		}
		else
		{
			$query = $this->getAttribute('query');
			if ( is_null( $query ) )
			{
				$result = $ar->find( array($ar->getPrimaryKey() => $this->recordId,
										$versionFieldName => 'PUBLISHED',
										$languageFieldName => $this->_application->getLanguageId()));
			}
			else
			{
				$result = $ar->load( $this->recordId, null, $query );
			}

			if (!$result)
			{
				// TODO
				// record non trovato
			}
			else
			{
				$this->_content	= $ar->getValuesAsArray($this->getAttribute('getRelations'));
			}
		}

		$this->_content['__url__'] = !is_null( $this->getAttribute( 'routeUrl' ) ) ? org_glizy_helpers_Link::makeURL( $this->getAttribute( 'routeUrl' ), $this->_content) : '';

		// crea i figli
		if ( $this->getAttribute( 'createModelComponents' ) )
		{
			$this->canHaveChilds = true;
			$className = $dataProvider->getRecordClassName();
			$this->createChildsFromModel($className);
			$this->processChilds();
			$this->canHaveChilds = false;
		}
	}

	function render()
	{
		$content = $this->getContent();
		$outputTitle = '';
		$outputImage = '';
		$outputProperty = '';
		$outputDescription = '';

		foreach( $this->items as $i )
		{
			$value = $content[ $i[ 'field' ] ];
			if ( glz_empty( $value ) ) continue;
			if ( is_array( $value ) && $value[ '__html__' ] )
			{
				$value = $value[ '__html__' ];
			}

			$renderCell = $i[ 'renderCell' ];
			if ( !empty( $renderCell ) )
			{
				if ( !is_object( $renderCell ) )
				{
					$renderCell = &org_glizy_ObjectFactory::createObject( $renderCell );
					$i[ 'renderCell' ] = $renderCell;
				}

				if ( is_object( $renderCell ) )
				{
					$value = $renderCell->renderCell( $value );
				}
			}

			$label = $i[ 'label' ];
			if (preg_match("/\{i18n\:.*\}/i", $label))
			{
				$code = preg_replace("/\{i18n\:(.*)\}/i", "$1", $label);
				$label = org_glizy_locale_Locale::getPlain($code);
			}

			if ( $i[ 'type' ] == 'title' )
			{
				$outputTitle .= '<h2>'.$value.'</h2>';
			}
			else if ( $i[ 'type' ] == 'property' )
			{
				$outputProperty .= '<li><span>'.$label.':</span> '.$value.'</li>';
			}
			else if ( $i[ 'type' ] == 'description' )
			{
				$outputDescription .= '<h3>'.$label.'</h3> '.$value;
			}
			else if ( $i[ 'type' ] == 'image' )
			{
				$outputImage .= $value;
			}
		}

		$cssClass = $this->getAttribute( 'cssClass' );

		if ( $outputProperty != '' )
		{
			$outputProperty = '<ul>'.$outputProperty.'</ul>';
		}

		if ( $outputImage != '' )
		{
			$outputImage = '<dic class="row"><div class="three columns images">'.$outputImage.'</div>';
			$outputProperty = '<div class="eight columns">'.$outputProperty.'</div></div>';
		}



		$output = '<div id="'.$this->getOriginalId().'"'.( $cssClass != "" ? ' class="'.$cssClass.'"' : '' ).'>';
		$output .= $outputTitle.$outputImage.$outputProperty.$outputDescription;
		$output .= '</div>';

		$this->addOutputCode( $output );
	}

	function getContent()
	{
		return $this->getChildContent();
	}

	function getChildContent()
	{
		$result = array();
		if (is_null($this->recordId)) return $result;

		if (count($this->childComponents))
		{
			for ($i=0; $i<count($this->childComponents);$i++)
			{
				$id = preg_replace('/^'.$this->getId().'\-/', '', $this->childComponents[$i]->getId());
				$r = $this->childComponents[$i]->getContent();
				if ($this->childComponents[$i]->_tagname=='glz:Groupbox')
				{
					// TODO
					// da risolvere in modo differente
					// ci sono TAG che sono solo container
					// quindi deveno passare il contenuto senza essere inseriti nel result
					// penso che il modo migliore
					// sia fare un componente dal quale derivano e poi verificare la subclass
					// oppure aggiungere una nuova proprietà a componentContainer
					// in fin dei conti se un componentContainer non accetta l'outputnon dovrebbe avere un contenuto proprio
					// ma questa è una cosa da verificare bene
					$result = array_merge($result, $r);
				}
				else
				{

					$result[$id] = $r;
				}
			}
			$result['__url__'] = $this->_content['__url__'];
			return $result;
		}
		else
		{
			return $this->_content;
		}
	}

	function loadContent($id)
	{
		$id = preg_replace('/^'.$this->getId().'\-/', '', $id);
		return isset($this->_content[$id]) ? $this->_content[$id] : '';
	}


	function addItem( $i )
	{
		$this->items[] = $i;
	}


	public static function compile($compiler, &$node, &$registredNameSpaces, &$counter, $parent='NULL', $idPrefix, $componentClassInfo, $componentId)
	{
		$compiler->_classSource .= '$n'.$counter.' = &org_glizy_ObjectFactory::createComponent(\''.$componentClassInfo['classPath'].'\', $application, '.$parent.', \''.$node->nodeName.'\', '.$idPrefix.'\''.$componentId.'\', \''.$componentId.'\', $skipImport)'.GLZ_COMPILER_NEWLINE;

		if ($parent!='NULL')
		{
			$compiler->_classSource .= $parent.'->addChild($n'.$counter.')'.GLZ_COMPILER_NEWLINE;
		}

		if (count($node->attributes))
		{
			// compila  gli attributi
			$compiler->_classSource .= '$attributes = array(';
			foreach ( $node->attributes as $key=>$value )
			{
				if ($key!='id')
				{
					$compiler->_classSource .= '\''.$key.'\' => \''.addslashes($value).'\', ';
				}
			}
			$compiler->_classSource .= ')'.GLZ_COMPILER_NEWLINE;
			$compiler->_classSource .= '$n'.$counter.'->setAttributes( $attributes )'.GLZ_COMPILER_NEWLINE;
		}

		foreach ($node->childNodes as $n )
		{
			if ( $n->nodeName == "glz:RecordDetailItem" )
			{
				$type = $n->hasAttribute( 'type' ) ? $n->getAttribute( 'type' ) : '';
				$field = $n->hasAttribute( 'field' ) ? $n->getAttribute( 'field' ) : '';
				$label = $n->hasAttribute( 'label' ) ? $n->getAttribute( 'label' ) : '';
				$renderCell = $n->hasAttribute( 'renderCell' ) ? $n->getAttribute( 'renderCell' ) : '';
				$compiler->_classSource .= '$n'.$counter.'->addItem( array( "type" => "'.$type.'", "field" => "'.$field.'", "label" => "'.$label.'", "renderCell" => "'.$renderCell.'" ) );';
			}
		}
	}
}