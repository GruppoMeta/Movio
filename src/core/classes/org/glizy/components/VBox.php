<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_components_VBox extends org_glizy_components_ComponentContainer
{

	function init()
	{
		$this->defineAttribute('cssClass', 	false, null, 	COMPONENT_TYPE_STRING);
		$this->defineAttribute('title', 	false, null, 	COMPONENT_TYPE_STRING);
		$this->defineAttribute('titleTag', 	false, 'h3', 	COMPONENT_TYPE_STRING);

		parent::init();
	}


	function render_html_onStart()
	{
		$attributes 		 	= array();
		$attributes['id'] 	= $this->getId();
		$attributes['class'] 	= $this->getAttribute('cssClass');
		$output = '<div '.$this->_renderAttributes($attributes).'>';
		$title = $this->getAttributeString('title');
		if (!empty($title))
		{
			$output .= '<'.$this->getAttribute('titleTag').'>'.$title.'</'.$this->getAttribute('titleTag').'>';
		}
		$this->addOutputCode($output);
	}

	function render_html_onEnd()
	{
		$output  = '</div>';
		$this->addOutputCode($output);
	}
}