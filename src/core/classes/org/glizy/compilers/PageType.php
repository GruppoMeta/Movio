<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_compilers_PageType extends org_glizy_compilers_Compiler
{
	var $_classSource		= '';
	var $_customClassSource	= '';
	var $_importedPaths = array();
	var $_className			= '';
	var $_path 				= false;
	var $mode 				= null;

	function compile($options)
	{
		$this->initOutput();

		if ( isset( $options[ 'pathTemplate' ] ) )
		{
			$fileName = $options[ 'pathTemplate' ].'/pageTypes/'.$options[ 'pageType' ];
			if ( file_exists( $fileName ) )
			{
				$this->_fileName = $fileName;
			}
		}
		if ( isset( $options[ 'mode' ] ) )
		{
			$this->mode = $options[ 'mode' ];
		}

		$pageXml = org_glizy_ObjectFactory::createObject( 'org.glizy.parser.XML' );
		if ($this->_fileName) {
			$pageXml->loadAndParseNS( $this->_fileName );
		} else {
			throw new Exception( 'PageType not found '.$options[ 'pageType' ] );
		}
		$pageRootNode 			= $pageXml->documentElement;
		$registredNameSpaces 	= $pageXml->namespaces;
		$registredNameSpaces['glz'] = 'org.glizy.components';
		// $idPrefix 				= isset($options['idPrefix']) ? $options['idPrefix'] : '';
		$this->_path			= $options['path'];

		// include i componenti usati
		foreach ($registredNameSpaces as $key=>$value)
		{
			if ($key!='glz' && substr($value, -1, 1)=='*' && !in_array($value, $this->_importedPaths))
			{
				glz_loadLocale($value);
				$this->output .= 'glz_loadLocale(\''.$value.'\')'.GLZ_COMPILER_NEWLINE;
				$this->_importedPaths[] = $value;
			}
		}

		$this->_className = glz_basename($this->_cacheObj->getFileName());
		$this->_classSource .= 'class '.$this->_className.'{'.GLZ_COMPILER_NEWLINE2;
		$this->_classSource .= 'function '.$this->_className.'(&$application, $skipImport=false, $idPrefix=\'\') {'.GLZ_COMPILER_NEWLINE2;
		$this->_classSource .= '$mode = "'.$this->mode.'"'.GLZ_COMPILER_NEWLINE;
		$counter = 0;
		$this->_compileXml($pageRootNode, $registredNameSpaces, $counter, '$application', '');
		$this->_classSource .= '}'.GLZ_COMPILER_NEWLINE2;
		$this->_classSource .= '}'.GLZ_COMPILER_NEWLINE2;

		$this->output .= $this->_classSource;
		$this->output .= $this->_customClassSource;

		return $this->save();
	}


	function _compileXml(&$node, &$registredNameSpaces, &$counter, $parent='NULL', $idPrefix='', $idPrefixOriginal='')
	{
		if ($node->nodeType == XML_COMMENT_NODE) return;

		$componentObj = null;
		$componentClassInfo = $this->_getComponentClassInfo($node->nodeName, $registredNameSpaces);
		if (!empty($componentClassInfo['classPath']) && !in_array($componentClassInfo['classPath'], $this->_importedPaths))
		{
			$this->_importedPaths[] = $componentClassInfo['classPath'];
		}
		$compileTranslateMethod = null;
		$compileMethod = null;
		$compileMethodAddPrefix = null;
		if (class_exists($componentClassInfo['className']))
		{
			try {
				$compileTranslateMethod = new ReflectionMethod( $componentClassInfo['className'].'::translateForMode_'.$this->mode );
			    if (!$compileTranslateMethod->isStatic()) $compileTranslateMethod = null;
			} catch (Exception $e) {}
			try {
			    $compileMethod = new ReflectionMethod( $componentClassInfo['className'].'::compile' );
			    if (!$compileMethod->isStatic()) $compileMethod = null;
			} catch (Exception $e) {}
			try {
				$compileMethodAddPrefix = new ReflectionMethod( $componentClassInfo['className'].'::compileAddPrefix' );
				if (!$compileMethodAddPrefix->isStatic()) $compileMethodAddPrefix = null;
			} catch (Exception $e) {}
		}

		if ($compileTranslateMethod) {
			$newNodeXml = $compileTranslateMethod->invoke(null, $node);
			if ($newNodeXml) {
				$partXml = org_glizy_ObjectFactory::createObject( 'org.glizy.parser.XML' );
				$partXml->loadXmlAndParseNS( $newNodeXml , LIBXML_NOERROR );
				$newNode = $partXml->documentElement;
				$this->addNamespace($partXml->namespaces, $registredNameSpaces);
				$this->_compileXml($newNode, $registredNameSpaces, $counter, $parent, $idPrefix, $idPrefixOriginal);
				$oldcounter = $counter;

				if (strpos($newNodeXml, 'noChild="true"')===false) {
					$this->compileChildren($node, $registredNameSpaces, $counter, $oldcounter, $idPrefix, $idPrefixOriginal );
				}
			}
			return;
		}

		// sostituisce i caratteri speciali all'interno del nome del tag
		// per poter verificare se � stato deifnito un metodo aaposito
		// per compilare il tag
		$methodName = 'compile_'.preg_replace('/[\-\#\:]/', '', $node->nodeName);
		if (method_exists($this, $methodName))
		{
			$this->$methodName($node, $registredNameSpaces, $counter, $parent, $idPrefix);
			return;
		}
		else
		{
			$this->_classSource .= '// TAG: '.$node->nodeName.GLZ_COMPILER_NEWLINE2;
			$componentId = $node->hasAttribute( 'id' ) ? $node->getAttribute( 'id' ) : 'c'.md5(uniqid(rand(), true));
			$componentIdPrefix = $idPrefix;
			$compileOnlyChild = false;

			if ($compileMethod)	{
				$r = $compileMethod->invoke(null, $this, $node, $registredNameSpaces, $counter, $parent, $idPrefix, $componentClassInfo, $componentId );
				if ($r!==true) {
					// non ha figli
					return true;
				}
				$compileOnlyChild = true;
			}
			if (!$compileOnlyChild) {
				if ($compileMethodAddPrefix) {
					$componentIdPrefix = $compileMethodAddPrefix->invoke(null, $this, $node, $componentId, $idPrefix);
				}

				$this->_classSource .= '$n'.$counter.' = &org_glizy_ObjectFactory::createComponent(\''.$componentClassInfo['classPath'].'\', $application, '.$parent.', \''.$node->nodeName.'\', $idPrefix.'.$idPrefix.'\''.$componentId.'\', '.$idPrefixOriginal.'\''.$componentId.'\', $skipImport, $mode)'.GLZ_COMPILER_NEWLINE;

				if ($parent!='NULL')
				{
					$this->_classSource .= $parent.'->addChild($n'.$counter.')'.GLZ_COMPILER_NEWLINE;
				}

				if (count($node->attributes))
				{
					// compila  gli attributi
					$this->_classSource .= '$attributes = array(';
					foreach ( $node->attributes as $index=>$attr )
					{
						if ($attr->name!='id')
						{
							// NOTA: su alcune versioni di PHP (es 5.1)  empty( $attr->prefix ) non viene valutato in modo corretto
							$prefix = $attr->prefix == "" ||  is_null( $attr->prefix ) ? "" : $attr->prefix.":";
							$this->_classSource .= '\''.$prefix.$attr->name.'\' => \''.str_replace('\'', '\\\'', $attr->value).'\', ';
						}
					}

					$this->_classSource .= ')'.GLZ_COMPILER_NEWLINE;
					$this->_classSource .= '$n'.$counter.'->setAttributes( $attributes )'.GLZ_COMPILER_NEWLINE;
				}
			}
			$idPrefix = $componentIdPrefix;
			$oldcounter = $counter;
			$this->compileChildren($node, $registredNameSpaces, $counter, $oldcounter, $idPrefix );
		}
	}

	function compileChildren(&$node, &$registredNameSpaces, &$counter, $oldcounter='NULL', $idPrefix='') {
		foreach( $node->childNodes as $nc )
		{
			$counter++;
			$this->_compileXml($nc, $registredNameSpaces, $counter, '$n'.$oldcounter, $idPrefix);
		}

		if ( $node->hasAttribute( 'allowModulesSnippets' )  && $node->getAttribute( 'allowModulesSnippets' ) == "true")
		{
			$modulesState = org_glizy_Modules::getModulesState();
			$modules = org_glizy_Modules::getModules();

			foreach( $modules as $m )
			{
				$isEnabled = !isset( $modulesState[ $m->id ] ) || $modulesState[ $m->id ];
				if ( $isEnabled && $m->pluginInPageType && $m->pluginSnippet )
				{
					$counter++;
					$this->compile_glzinclude( $m->pluginSnippet, $registredNameSpaces, $counter, '$n'.$oldcounter, $idPrefix );
				}
			}
		}
	}

	function compileChildNode(&$node, &$registredNameSpaces, &$counter, $oldcounter='NULL', $idPrefix='')
	{
		$this->_compileXml($node, $registredNameSpaces, $counter, '$n'.$oldcounter, $idPrefix);
	}

	function _getComponentClassInfo($componentName, &$registredNameSpaces)
	{
		$result = array('className' => '', 'classPath' => '');
		$componentClassName = explode(':', $componentName);
		if (count($componentClassName)==2)
		{
			$nameSpace 			= $componentClassName[0];
			$componentClassName = $componentClassName[1];
			if (array_key_exists($nameSpace, $registredNameSpaces))
			{
				if ($registredNameSpaces[$nameSpace]!='*')
				{
					$componentClassName = rtrim($registredNameSpaces[$nameSpace], '.*').'.'.$componentClassName;
				}
				$result['classPath'] = $componentClassName;
				$componentClassName = str_replace('*', '', $componentClassName);
				$componentClassName = str_replace('.', '_', $componentClassName);
				$result['className'] = $componentClassName;
			}
			else
			{
				// TODO
				// namespace non definito
				// visualizzare un errore

			}
		}
		else
		{
			$result['className'] = $componentName;
		}
		return $result;
	}

	function compile_cdatasection(&$node, &$registredNameSpaces, &$counter, $parent='NULL', $idPrefix='')
	{
		$this->compile_text($node, $registredNameSpaces, $counter, $parent);
	}

	function compile_text(&$node, &$registredNameSpaces, &$counter, $parent='NULL', $idPrefix='')
	{
		$this->_classSource .= '$tagContent = <<<EOD'.GLZ_COMPILER_NEWLINE2;
		$this->_classSource .=  str_replace('$', '\$', $node->nodeValue).GLZ_COMPILER_NEWLINE2;
		$this->_classSource .= 'EOD'.GLZ_COMPILER_NEWLINE;
		$this->_classSource .= $parent.'->setContent($tagContent)'.GLZ_COMPILER_NEWLINE;
	}


	function compile_glzif(&$node, &$registredNameSpaces, &$counter, $parent='NULL', $idPrefix='')
	{
		$condition = $node->getAttribute( "condition" );
		$condition = str_replace( array( '$application.', '$user.' ), array( '$application->', '$application->getCurrentUser()->' ), $condition );
		$this->_classSource .= 'if ('.$condition.') {'.GLZ_COMPILER_NEWLINE2;
		foreach( $node->childNodes as $nc )
		{
			$this->_compileXml($nc, $registredNameSpaces, $counter, $parent, $idPrefix);
		}
		$this->_classSource .= '} // end if'.GLZ_COMPILER_NEWLINE2;
	}


	function compile_glztemplateDefine(&$node, &$registredNameSpaces, &$counter, $parent='NULL', $idPrefix='')
	{
	}

	function compile_glzinclude(&$node, &$registredNameSpaces, &$counter, $parent='NULL', $idPrefix='')
	{
		$origSrc = is_object( $node ) ? $node->getAttribute( 'src' ) : $node;
		$src = $origSrc;
		if ( strpos( $src, '.xml' ) === false )
		{
			$src .= '.xml';
		}
		if ( file_exists( dirname( $this->_fileName ).'/'.$src ) )
		{
			$src = dirname( $this->_fileName ).'/'.$src;
		}
		else
		{
			$src = glz_findClassPath( $origSrc );
		}
		if ( is_null( $src ) )
		{
			die( '[ERROR] glz:include file not found: '.$node->getAttribute( 'src' ) );
		}
		$this->_classSource .= '// include: '.$src.GLZ_COMPILER_NEWLINE2;


		$srcXml = file_get_contents($src);
		// esegue il parsing per sapere quale sono i parametri del template e ricavare quelli di default
		$templateParams = array();
		$includeXML = org_glizy_ObjectFactory::createObject( 'org.glizy.parser.XML' );
		$includeXML->loadXmlAndParseNS( $srcXml );
		$templateDefineNodes = $includeXML->getElementsByTagName('templateDefine');
		foreach ($templateDefineNodes as $templateDefine) {
			if (!$templateDefine->hasAttribute('name')) {
				throw org_glizy_compilers_PageTypeException::templateDefineNotValid($src);
			}
			$templateParams[$templateDefine->getAttribute('name')] = $templateDefine->hasAttribute('required') && $templateDefine->getAttribute('required')=='true' ? null :
																				( $templateDefine->hasAttribute('defaultValue') ? $templateDefine->getAttribute('defaultValue') : '');

		}

		if (count($templateParams)) {
			$templateParamsKeys = array_keys($templateParams);
			foreach( $node->childNodes as $nc ) {
				if ($nc->tagName=='glz:template' && $nc->hasAttribute('name')) {
					$name = $nc->getAttribute('name');
					if (!in_array($name, $templateParamsKeys)) {
						throw org_glizy_compilers_PageTypeException::templateDefinitionDontExixts($name);
					}

					$value = '';
					if ($nc->hasAttribute('value')) {
						$value = $nc->getAttribute('value');
					} else {
						$tempDom = new DOMDocument();
						foreach( $nc->childNodes as $ncc ) {
	     					$tempDom->appendChild($tempDom->importNode($ncc,true));
	     				}
	     				$value = $tempDom->saveXML();
	     				$value = str_replace('<?xml version="1.0"?>', '', $value);
					}

					$templateParams[$name] = $value;
				}
			}

			foreach($templateParams as $k=>$v) {
				if (is_null($v)) {
					throw org_glizy_compilers_PageTypeException::templateDefinitionRequired($k, $src);
				}
				$srcXml = str_replace( '##'.$k.'##', $v, $srcXml );
			}
		}

		$includeXML = org_glizy_ObjectFactory::createObject( 'org.glizy.parser.XML' );
		$includeXML->loadXmlAndParseNS( $srcXml );
		$newNameSpaces = $includeXML->namespaces;
		$this->addNamespace($newNameSpaces, $registredNameSpaces);
		if ( $includeXML->documentElement->hasAttribute( 'adm:editComponents' ) )
		{
			$this->_classSource .= '$n0->setAttribute( "adm:editComponents", "'.$includeXML->documentElement->getAttribute( 'adm:editComponents' ).'" )'.GLZ_COMPILER_NEWLINE;
		}

		$this->_compileXml($includeXML->documentElement, $registredNameSpaces, $counter, $parent, $idPrefix);
	}

	function compile_baseTag(&$node, &$registredNameSpaces, &$counter, $parent='NULL', $idPrefix, $componentClassInfo, $componentId)
	{
		$this->_classSource .= '$n'.$counter.' = &org_glizy_ObjectFactory::createComponent(\''.$componentClassInfo['classPath'].'\', $application, '.$parent.', \''.$node->nodeName.'\', $idPrefix.'.$idPrefix.'\''.$componentId.'\', \''.$componentId.'\', $skipImport)'.GLZ_COMPILER_NEWLINE;

		if ($parent!='NULL')
		{
			$this->_classSource .= $parent.'->addChild($n'.$counter.')'.GLZ_COMPILER_NEWLINE;
		}

		if (count($node->attributes))
		{
			// compila  gli attributi
			$this->_classSource .= '$attributes = array(';
			foreach ( $node->attributes as $index=>$attr )
			{
				if ($attr->name!='id')
				{
					// NOTA: su alcune versioni di PHP (es 5.1)  empty( $attr->prefix ) non viene valutato in modo corretto
					$prefix = $attr->prefix == "" ||  is_null( $attr->prefix ) ? "" : $attr->prefix.":";
					$this->_classSource .= '\''.$prefix.$attr->name.'\' => \''.addslashes($attr->value).'\', ';
				}
			}

			$this->_classSource .= ')'.GLZ_COMPILER_NEWLINE;
			$this->_classSource .= '$n'.$counter.'->setAttributes( $attributes )'.GLZ_COMPILER_NEWLINE;
		}
	}

	function getPath()
	{
		return $this->_path;
	}

	private function addNamespace($newNameSpaces, &$registredNameSpaces)
	{
		foreach ($newNameSpaces as $key=>$value)
		{
			if ( isset( $registredNameSpaces[ $key ] ) ) continue;

			if ($key!='glz' && substr($value, -1, 1)=='*' && !in_array($value, $this->_importedPaths))
			{
				$this->_importedPaths[] = $value;
			}
			$registredNameSpaces[ $key ] = $value;
		}
	}
}
