<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizy_template_skin_TWIG extends org_glizy_template_skin_Skin
{
	static $isLibLoaded = false;
	private $templateData = array();

	function __construct($fileName='', $defaultHtml='')
	{
		if (!self::$isLibLoaded) {
			self::$isLibLoaded = true;
			require_once(GLZ_LIBS_DIR.'/Twig/Autoloader.php');
			Twig_Autoloader::register();
		}

		parent::__construct($fileName, $defaultHtml);

		$loader = new Twig_Loader_Filesystem($this->filePath);
		$this->_templClass = new Twig_Environment($loader, array(
		    'cache' => org_glizy_Paths::getRealPath('CACHE'),
		    'auto_reload' => true,
		    'debug' => true
		));
		$this->_templClass->addExtension(new Twig_Extension_Debug());
	}

	function set($theBlock, $theValue)
	{
		$this->templateData[$theBlock] = $theValue;
	}

	function execute()
	{
		$res = $this->_templClass->render($this->fileName, $this->templateData);
		if ( preg_match( "/^.*<body[^>]*>(.*)<\/body>.*$/si", $res ) )
		{
			$res = preg_replace("/^.*<body[^>]*>(.*)<\/body>.*$/si", "$1", $res);
		}
		return $res;
	}
}
