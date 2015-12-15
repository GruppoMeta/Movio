<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizy_template_skin_Skin extends GlizyObject
{
	var $filePath;
	var $fileName;
	var $_templClass	= NULL;

	function __construct($fileName='', $defaultHtml='')
	{
		$this->filePath = org_glizy_Paths::getRealPath('APPLICATION_TEMPLATE', 'skins');

		if ( !empty( $this->filePath ) ) {
			$this->filePath .= '/';
		}

		if (!empty($defaultHtml)) {
			$compiler 			= org_glizy_ObjectFactory::createObject('org.glizy.compilers.Skin');
			$compiledFileName 	= $compiler->verify($this->filePath.$fileName, array('defaultHtml' => $defaultHtml));

			$this->fileName 	= basename($compiledFileName);
			$this->filePath 	= dirname($compiledFileName).'/';
		} else {
			if ( org_glizy_ObjectValues::get( 'org.glizy.application', 'pdfMode' ) ) {
				// check if is available a pdf skin
				$pdfFilePath = preg_replace( '/\/skins\/$/', '/skins-pdf/', $this->filePath );
				if ( file_exists($pdfFilePath.$fileName) ) {
					$this->filePath = $pdfFilePath;
				} else {
					$pdfFilePath = org_glizy_Paths::getRealPath('APPLICATION_TEMPLATE_DEFAULT', 'skins-pdf').'/';
					if ( file_exists($pdfFilePath.$fileName) ) {
						$this->filePath = $pdfFilePath;
					}
				}
			}
			if (file_exists($fileName) ) {
				$this->filePath = '';
			} else if ( !file_exists($this->filePath.$fileName) ) {
				$this->filePath = org_glizy_Paths::getRealPath('APPLICATION_TEMPLATE_DEFAULT', 'skins').'/';
			} else if (!file_exists($this->filePath.$fileName) && file_exists(org_glizy_Paths::getRealPath('APPLICATION', 'skins').'/'.$fileName)) {
				$this->filePath = org_glizy_Paths::getRealPath('APPLICATION', 'skins').'/';
			}
			$this->fileName = $fileName;
		}
	}
}