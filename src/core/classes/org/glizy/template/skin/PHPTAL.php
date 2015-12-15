<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizy_template_skin_PHPTAL extends org_glizy_template_skin_Skin
{
	private static $isLibLoaded = false;

	function __construct($fileName='', $defaultHtml='')
	{
		if (!self::$isLibLoaded) {
			self::$isLibLoaded = true;
			set_include_path(get_include_path() . PATH_SEPARATOR . GLZ_LIBS_DIR . 'PHPTAL5/');
			set_include_path(get_include_path() . PATH_SEPARATOR . GLZ_LIBS_DIR . 'PHPTAL5/PHPTAL/');
			require_once(GLZ_LIBS_DIR.'PHPTAL5/PHPTAL.php');
		}

		parent::__construct($fileName, $defaultHtml);
		$this->_templClass = new PHPTAL();

		$this->_templClass->setPhpCodeDestination(org_glizy_Paths::getRealPath('CACHE'))
				->setTemplate($this->filePath.$this->fileName)
				->setForceReparse(false)
				->setEncoding( __Config::get('CHARSET'));
	}

	function set($theBlock, $theValue)
	{
		$this->_templClass->set($theBlock, $theValue);
	}

	function execute()
	{
		$res = $this->_templClass->execute();
		if ( preg_match( "/^.*<body[^>]*>(.*)<\/body>.*$/si", $res ) )
		{
			$res = preg_replace("/^.*<body[^>]*>(.*)<\/body>.*$/si", "$1", $res);
		}
		return $res;
	}
}
