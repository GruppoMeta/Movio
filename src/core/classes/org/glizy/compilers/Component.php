<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_compilers_Component extends org_glizy_compilers_PageType
{

    /**
     * @param $options
     *
     * @return mixed
     */
	function compile($options)
	{
		$this->initOutput();
		if ( isset( $options[ 'mode' ] ) )
		{
			$this->mode = $options[ 'mode' ];
		}

		$pageXml = org_glizy_ObjectFactory::createObject( 'org.glizy.parser.XML' );
		$pageXml->loadAndParseNS( $this->_fileName );
		$pageRootNode 			= $pageXml->documentElement;
		$registredNameSpaces 	= $pageXml->namespaces;
		$registredNameSpaces['glz'] = 'org.glizy.components';

		// include i componenti usati
		foreach ($registredNameSpaces as $key=>$value)
		{
			if ($key!='glz' && substr($value, -1, 1)=='*')
			{
				$this->output .= 'glz_loadLocale(\''.$value.'\')'.GLZ_COMPILER_NEWLINE;
			}
		}

		$className = glz_basename($this->_cacheObj->getFileName());
		$componentClassInfo = $this->_getComponentClassInfo($pageRootNode->nodeName, $registredNameSpaces);

		//if (!empty($componentClassInfo['classPath']))
		//{
		//	$this->_classSource .= 'glz_import(\''.$componentClassInfo['classPath'].'\')'.GLZ_COMPILER_NEWLINE;
		//}

		$this->_classSource .= 'class '.$className.' extends '.$componentClassInfo['className'].' {'.GLZ_COMPILER_NEWLINE2;
		$this->_classSource .= 'function '.$className.'(&$application, &$parent, $tagName=\'\', $id=\'\', $originalId=\'\', $skipImport=false) {'.GLZ_COMPILER_NEWLINE2;
		if (isset($options['originalClassName'])) $this->_classSource .= '$this->_className = \''.$options['originalClassName'].'\''.GLZ_COMPILER_NEWLINE;
		$this->_classSource .= 'parent::__construct($application, $parent, $tagName, $id, $originalId)'.GLZ_COMPILER_NEWLINE;
		$this->_classSource .= '$mode = ""'.GLZ_COMPILER_NEWLINE;
		$this->_classSource .= '$idPrefix = ""'.GLZ_COMPILER_NEWLINE;
		$this->_classSource .= '$n0 = &$this'.GLZ_COMPILER_NEWLINE;
		$this->_classSource .= 'if (!empty($id)) $id .= \'-\''.GLZ_COMPILER_NEWLINE;
		$this->_classSource .= 'if (!empty($originalId)) $originalId .= \'-\''.GLZ_COMPILER_NEWLINE;

		if (count($pageRootNode->attributes))
		{
			// compila  gli attributi
			$this->_classSource .= '$attributes = array(';
			foreach ( $pageRootNode->attributes as $index=>$attr )
			{
				if ($attr->name!='id')
				{
					// NOTA: su alcune versioni di PHP (es 5.1)  empty( $attr->prefix ) non viene valutato in modo corretto
					$prefix = $attr->prefix == "" ||  is_null( $attr->prefix ) ? "" : $attr->prefix.":";
					$this->_classSource .= '\''.$prefix.$attr->name.'\' => \''.addslashes($attr->value).'\', ';
				}
			}
			$this->_classSource .= ')'.GLZ_COMPILER_NEWLINE;
			$this->_classSource .= '$this->setAttributes( $attributes )'.GLZ_COMPILER_NEWLINE;
		}

		$counter = 0;
		$oldcounter = $counter;
		foreach( $pageRootNode->childNodes as $nc )
		{
			$counter++;
			$this->_compileXml($nc, $registredNameSpaces, $counter, '$n'.$oldcounter, '$id.', '$originalId.' );
		}

		if (isset($options['originalClassName']) && $pageRootNode->hasAttribute( 'allowModulesSnippets' )  && $pageRootNode->getAttribute( 'allowModulesSnippets' ) == "true" )
		{
			$modulesState = org_glizy_Modules::getModulesState();
			$modules = org_glizy_Modules::getModules();

			foreach( $modules as $m )
			{
				$isEnabled = !isset( $modulesState[ $m->id ] ) || $modulesState[ $m->id ];
				if ( $isEnabled && $m->pluginInPageType && $m->pluginSnippet )
				{
					$counter++;
					$this->compile_glzinclude( $m->pluginSnippet, $registredNameSpaces, $counter, '$n'.$oldcounter, '$id.' );
				}
			}
		}
		$this->_classSource .= '}'.GLZ_COMPILER_NEWLINE2;
		$this->_classSource .= '}'.GLZ_COMPILER_NEWLINE2;

		$this->output .= $this->_classSource;
		$this->output .= $this->_customClassSource;

		return $this->save();
	}
}