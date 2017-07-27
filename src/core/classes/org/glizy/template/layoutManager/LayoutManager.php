<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizy_template_layoutManager_LayoutManager extends GlizyObject
{
	var $fileName;
	var $rootPath;
	var $pathPrefix = '';
	var $language = '';
	var $currentMenu = '';

	function __construct($fileName='', $rootPath='')
	{
		$this->fileName = org_glizy_Paths::getRealPath('APPLICATION_TEMPLATE', $this->pathPrefix.$fileName);
		$this->rootPath = $rootPath ? : org_glizy_Paths::get('APPLICATION_TEMPLATE');

		$application = &org_glizy_ObjectValues::get('org.glizy', 'application');
		$this->currentMenu = $application->getCurrentMenu();
		$this->language = $application->getLanguage();

		if ( !file_exists( $this->fileName ) )
		{
			org_glizy_Exception::show( 500, "Template non trovato: ".$this->rootPath.$this->pathPrefix.$fileName, "", "");
			exit;
		}
	}

	function apply($pageXmlOutput, $title, $subtitle)
	{
		return $pageXmlOutput;
	}

	function modifyBodyTag($value, $templateSource)
	{
		return str_replace('<body', '<body '.$value, $templateSource);
	}

	function checkRequiredValues( &$regionContent )
	{
		if (!isset($regionContent['docTitle']))
		{
			$regionContent['docTitle'] = $this->currentMenu->title.' - '.__Config::get('APP_NAME');
		}
		// compatibility fix
		$regionContent['doctitle'] = $regionContent['docTitle'];
	}


	function fixUrl( $templateSource )
	{
		$templateSource = preg_replace("/<(.*?)(href|src|background)\s*=\s*(\'|\")(?!((http|https|ftp|mailto|javascript):|<\?php|\/\/))(.*?)(\'|\")(.*?)>/si", "<$1$2=$3".$this->rootPath.$this->pathPrefix."$6$7$8>", $templateSource);

		$templateSource = preg_replace("/(\s+url\s*?\([\'\"]*)(?!((http|https):))(.*?)([\'\"]*\))/i", "$1".$this->rootPath."$4$5", $templateSource);

		return $templateSource;
	}

	function fixLanguages( $templateSource )
	{
		$templateSource = str_replace( '<head>', '<head><base href="'.GLZ_HOST.'/" />', $templateSource );
		$templateSource = preg_replace('/(\<html.*xml:lang=)"([^"]*)"/', '$1"'.$this->language.'"', $templateSource );
		$templateSource = preg_replace('/(\<html.*lang=)"([^"]*)"/', '$1"'.$this->language.'"', $templateSource );

		if ( org_glizy_Config::get('SEF_URL') === 'full' )
		{
			// non è il massimo ma la regexp su testi lunghi crasha
			$templateSource = str_replace( array( 'href="#', 'href=\'#', 'href="noTranslate:' ), array( 'href="'.__Routing::scriptUrl().'#', 'href=\''.__Routing::scriptUrl().'#', 'href="'), $templateSource );
			//$newtemplateSource = preg_replace("/<(.*?)(href)\s*=\s*(\'|\")(\#.*)(\'|\")(.*?)>/si", "<$1$2=$3".__Routing::scriptUrl()."$4$5$6>", $templateSource);
			// if ( !empty( $newtemplateSource ) )
			// 			{
			// 				$templateSource = &$newtemplateSource;
			// 			}
		}
		return $templateSource;
	}
}