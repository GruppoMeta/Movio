<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_components_Panel extends org_glizy_components_ComponentContainer
{
	var $_isEnabled;

	/**
	 * Init
	 *
	 * @return	void
	 * @access	public
	 */
	function init()
	{
		// define the custom attributes
		$this->defineAttribute('tag',		false, 	'div',	COMPONENT_TYPE_STRING);
		$this->defineAttribute('cssClass', 	false, '', 	COMPONENT_TYPE_STRING);
		$this->defineAttribute('text', 		false, '', 	COMPONENT_TYPE_STRING);
		$this->defineAttribute('adm:showControl', 	false,	false,	COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('addInnerDiv', 	false,	false,	COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('innerCssClass', 	false, '', 	COMPONENT_TYPE_STRING);

		// call the superclass for validate the attributes
		parent::init();
	}


	function process()
	{
		$this->_isEnabled = $this->getAttribute('adm:showControl') ? $this->_parent->loadContent($this->getId())=="1" : true;


		if ($this->_isEnabled)
		{
			$this->processChilds();
		}
	}

	function render($outputMode=NULL, $skipChilds=false)
	{
		if ($this->_isEnabled || $outputMode != 'html')
		{
			parent::render( $outputMode, $skipChilds );
		}
	}

	/**
	 * Render
	 *
	 * @return	void
	 * @access	public
	 */
	function render_html_onStart()
	{
		$innserCssClass = $this->getAttribute('innerCssClass');
		$attributes 		 	= array();
		$attributes['id']		= $this->getId();
		$attributes['class'] 	= $this->getAttribute('cssClass');
		$this->addOutputCode('<'.$this->getAttribute('tag').' '.$this->_renderAttributes($attributes).'>'.
				( $this->getAttribute( 'addInnerDiv' ) ? '<div'.($this->_renderAttributes(array('class' => $innserCssClass))).'>' : '' ).
				$this->getAttribute('text')
				 );
	}

	function render_html_onEnd()
	{
		$this->addOutputCode( ( $this->getAttribute( 'addInnerDiv' ) ? '</div>' : '' ).'</'.$this->getAttribute('tag').'>');
	}
}