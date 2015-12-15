<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizy_template_skin_PHP extends org_glizy_template_skin_Skin
{
	private $_values;

	function __construct($fileName='', $defaultHtml='')
	{
		$this->_values = array();
		parent::__construct($fileName, $defaultHtml);
	}

	function set($theBlock, $theValue)
	{
		$this->_values[$theBlock] = $theValue;
	}

	function execute()
	{
		foreach (array_keys($this->_values) as $k)
		{
			$$k =& $this->_values[$k];
		}

		unset($k);
		ob_start();
		include($this->filePath.$this->fileName);
		$ret = ob_get_contents();
		ob_end_clean();
		return $ret;
	}
}