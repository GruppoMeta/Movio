<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_components_HtmlComponent extends org_glizy_components_ComponentContainer
{
	/**
	 * Init
	 *
	 * @return	void
	 * @access	public
	 */
	function init()
	{
		$this->defineAttribute('cssClass',	false, 	NULL,	COMPONENT_TYPE_STRING);

		// call the superclass for validate the attributes
		parent::init();
	}

	/*
	function getHtmlTag()
	{
		return $this->_htmlTag;
	}

	function setHtmlTag($value)
	{
		$this->_htmlTag	= $value;
	}
	*/

	/**
	 * Process
	 *
	 * @return	boolean	false if the process is aborted
	 * @access	public
	 */
	function process()
	{
		$tagContent = $this->getText();
		if (empty($tagContent))
		{
			// richiede il contenuto al padre
			$tagContent = $this->_parent->loadContent($this->getId());
			$this->setText($tagContent);
		}

		$this->processChilds();
	}

	function getContent()
	{
		return glz_encodeOutput($this->getText());
	}
}