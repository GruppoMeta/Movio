<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */



class org_glizy_components_Caption extends org_glizy_components_Component
{
	var $_caption = NULL;

	/**
	 * Init
	 *
	 * @return	void
	 * @access	public
	 */
	function init()
	{
		// define the custom attributes
		$this->defineAttribute('accesskey',		false, 	NULL,	COMPONENT_TYPE_STRING);			// TODO
		$this->defineAttribute('crop',			false, 	NULL,	COMPONENT_TYPE_STRING);		// TODO
		$this->defineAttribute('image',			false, 	NULL,	COMPONENT_TYPE_STRING);		// TODO
		$this->defineAttribute('label',			false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('tabindex',		false, 	NULL,	COMPONENT_TYPE_INTEGER);		// TODO

		// call the superclass for validate the attributes
		parent::init();
	}

	function process()
	{
		$this->_content = $this->getAttribute('label');
	}

	function render_html()
	{
		if (!is_null($this->_content))
		{
			$output  = '<legend>'.$this->_content.'</legend>';
			$this->addOutputCode($output);
		}
	}
}