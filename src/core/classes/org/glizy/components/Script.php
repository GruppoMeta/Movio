<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */



class org_glizy_components_Script extends org_glizy_components_NullComponent
{


	public static function compile($compiler, &$node, &$registredNameSpaces, &$counter, $parent='NULL', $idPrefix, $componentClassInfo, $componentId)
	{
		if ( $node->hasAttribute( 'extendParent' ) )
		{
			$scriptClassName = $compiler->_className.'__class__'.$counter;
			preg_match('/(\\'.$parent.'\s*=\s*&\s*org_glizy_ObjectFactory::createComponent\(\')([^\']*)(.*)/u', $compiler->_classSource, $matches, PREG_OFFSET_CAPTURE);
			$originalClassName = str_replace('.', '_', $matches[2][0]);
			$compiler->_customClassSource .= 'class '.$scriptClassName.' extends '.$originalClassName.GLZ_COMPILER_NEWLINE2;
			$compiler->_customClassSource .= '{'.$node->nodeValue.GLZ_COMPILER_NEWLINE2.'}'.GLZ_COMPILER_NEWLINE2;
			$compiler->_classSource = preg_replace('/(\\'.$parent.'\s*=\s*&\s*org_glizy_ObjectFactory::createComponent\(\')([^\']*)/', '$1'.$scriptClassName, $compiler->_classSource);
		}
		else if ( $node->hasAttribute( 'target' ) )
		{
			$scriptClassName = $compiler->_className.'__class__'.$counter;
			$compiler->_customClassSource .= 'class '.$scriptClassName.GLZ_COMPILER_NEWLINE2;
			$compiler->_customClassSource .= '{'.$node->nodeValue.GLZ_COMPILER_NEWLINE2.'}'.GLZ_COMPILER_NEWLINE2;
			$compiler->_classSource .= '$'.$scriptClassName.' = &'.$parent.'->getComponentById(\''.$node->attributes['target'].'\')'.GLZ_COMPILER_NEWLINE;
			$compiler->_classSource .= '$'.$scriptClassName.'->setCustomClass(\''.$scriptClassName.'\')'.GLZ_COMPILER_NEWLINE;
		}
		else
		{
			$compiler->output .= $node->nodeValue.GLZ_COMPILER_NEWLINE;
		}
	}
}