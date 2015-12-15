<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */



class org_glizy_components_Import extends org_glizy_components_NullComponent
{
	public static function compile($compiler, &$node, &$registredNameSpaces, &$counter, $parent='NULL')
	{
		if ($node->hasAttribute('src'))
		{
			$src = $node->getAttribute('src');
			if (strpos($src, '.xml')===strlen($src)-4) {
				$src = substr($src, 0, -4);
			}

			$pageType = org_glizy_ObjectFactory::resolvePageType($src).'.xml';
	        $path = $compiler->getPath();
	        $fileName = $path.$pageType;

	        if ( !file_exists( $fileName ) )
	        {
	            $fileName = glz_findClassPath( $src );
	            if ( is_null( $fileName ) )
	            {
	                // TODO: file non trovato visualizzare errore
	            }
	        }

			$compiler2 = org_glizy_ObjectFactory::createObject('org.glizy.compilers.Component');
			$compiledFileName = $compiler2->verify($fileName);

			$className = GLZ_basename($compiledFileName);
			$componentId = $node->hasAttribute('id') ? $node->getAttribute('id') : '';
			$compiler->_classSource .= '// TAG: '.$node->nodeName.' '.$node->getAttribute('src').GLZ_COMPILER_NEWLINE2;
			$compiler->_classSource .= 'if (!$skipImport) {'.GLZ_COMPILER_NEWLINE2;
			$compiler->_classSource .= 'org_glizy_ObjectFactory::requireComponent(\''.$compiledFileName.'\', \''.addslashes($fileName).'\')'.GLZ_COMPILER_NEWLINE;
			$compiler->_classSource .= '$n'.$counter.' = new '.$className.'($application, '.$parent.')'.GLZ_COMPILER_NEWLINE;
			$compiler->_classSource .= $parent.'->addChild($n'.$counter.')'.GLZ_COMPILER_NEWLINE;
			$compiler->_classSource .= '}'.GLZ_COMPILER_NEWLINE;
		}
	}
}