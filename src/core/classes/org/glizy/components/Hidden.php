<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_components_Hidden extends org_glizy_components_HtmlFormElement
{
	/**
	 * Init
	 *
	 * @return	void
	 * @access	public
	 */
	function init()
	{
		$this->_hiddenItem = true;

		// define the custom attributes
		$this->defineAttribute('bindTo',			false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('value',				false, 	NULL,		COMPONENT_TYPE_STRING);
		$this->defineAttribute('name',				false, 	NULL,	COMPONENT_TYPE_STRING);

		// call the superclass for validate the attributes
		parent::init();
	}

	/**
	 * Process
	 *
	 * @return	boolean	false if the process is aborted
	 * @access	public
	 */
	function process()
	{
		$this->_content = $this->getAttribute('value');
		if (is_object($this->_content))
		{
			$contentSource = &$this->getAttribute('value');
			$this->_content = $contentSource->loadContent($this->getId(), $this->getAttribute('bindTo'));
		}
		else if (is_null($this->_content))
		{
			$this->_content = $this->_parent->loadContent($this->getId(), $this->getAttribute('bindTo'));
		}

		if (method_exists($this->_parent, 'setFilterValue'))
		{
			$bindTo = $this->getAttribute('bindTo');
			$this->_parent->setFilterValue(!empty($bindTo) ? $bindTo : $this->getId(), $this->_content);
		}

		$this->processChilds();
	}

	function render_html()
	{
		$name = $this->getAttribute( 'name' );
		if ($this->_content === true) {
			$this->_content = 'true';
		}
		if ($this->_content === false) {
			$this->_content = 'false';
		}
		$output = org_glizy_helpers_Html::hidden( empty( $name ) ? $this->getOriginalId() : $name ,
			!is_object($this->_content) && !is_array($this->_content) ? $this->_content : json_encode($this->_content),
			array( 'class' => $this->getAttribute( 'cssClass' )),
			$this->getAttribute( 'data' )
			 );
		$this->addOutputCode($this->applyItemTemplate('', $output));
	}
}