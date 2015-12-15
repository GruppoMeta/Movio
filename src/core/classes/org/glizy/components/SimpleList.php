<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_components_SimpleList extends org_glizy_components_ComponentContainer
{
	var $_items;

	/**
	 * Init
	 *
	 * @return	void
	 * @access	public
	 */
	function init()
	{
		// define the custom attributes
		$this->defineAttribute('cssClass',		false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('attributeToSelect',	false, 	'id', 	COMPONENT_TYPE_STRING);
		$this->defineAttribute('cssClassCurrent',	false, 	'current', 	COMPONENT_TYPE_STRING);

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
		$this->_items = array();
		// legge i valori dai figli
		for ($i=0; $i<count($this->childComponents);$i++)
		{
			if (method_exists($this->childComponents[$i], 'getItem') &&
				$this->childComponents[$i]->getAttribute( 'visible' ) &&
				$this->childComponents[$i]->getAttribute( 'enabled' ) )
			{
				$item = $this->childComponents[$i]->getItem();
				if ( is_array( $item ) )
				{
					$this->_items[] = $item;
				}
			}
		}

		$this->processChilds();
	}

	function render_html()
	{
		$output = '<ul id="'.$this->getId().'"'.(!is_null($this->getAttribute('cssClass')) ? ' class="'.$this->getAttribute('cssClass').'"' : '').'>';
		foreach($this->_items as $item)
		{
			$output .= '<li';
			$output .=  $item['selected'] ? ' '.$this->getAttribute('attributeToSelect').'="'.$this->getAttribute('cssClassCurrent').'">' : '>';
			if ( isset( $item[ 'url' ] ) )
			{
				$output .= $item[ 'url' ];
			}
			else if ( !empty( $item['key'] ) )
			{
				$output .= org_glizy_helpers_Link::makeLink($item['key'], array('title' => $item['value'], 'cssClass' => @$item['cssClass'] ), array(), '', false );
			}
			else
			{
				$output .= $item['value'];
			}
			$output .= '</li>';
		}
		$output .= '</ul>';
		$this->addOutputCode($output);
	}
}