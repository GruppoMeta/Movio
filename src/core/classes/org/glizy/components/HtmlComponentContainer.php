<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_components_HtmlComponentContainer extends org_glizy_components_HtmlComponent
{
	var $_output;
	var $acceptOutput;
	var $overrideEditableRegion;

	function __construct(&$application, &$parent, $tagName='', $id='', $originalId='')
	{
		parent::__construct($application, $parent, $tagName, $id, $originalId);
		$this->canHaveChilds	= true;
		$this->_output 			= array();
		$this->acceptOutput 	= false;
		$this->overrideEditableRegion 	= true;
	}


	function render_html()
	{
		$this->renderChilds();
		$ouput = '';
		for ($i=0; $i<count($this->_output); $i++)
		{
			$ouput .= $this->_output[$i]['code'];
		}
		$this->setText($ouput);
		$renderString = $this->_render();

		$this->addParentOutputCode($renderString);
	}
}