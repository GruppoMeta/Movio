<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_components_JSTab extends org_glizy_components_ComponentContainer
{
	function init()
	{
		// define the custom attributes
		$this->defineAttribute('label', true, '', COMPONENT_TYPE_STRING);
		$this->defineAttribute('cssClass', false, 'tab-pane', COMPONENT_TYPE_STRING);
		$this->defineAttribute('cssClassTab', false, '', COMPONENT_TYPE_STRING);
		$this->defineAttribute('disabled', false, false, COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('dropdown', false, false, COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('routeUrl', false, NULL, COMPONENT_TYPE_STRING);

		// call the superclass for validate the attributes
		parent::init();
	}

	function render_html_onStart()
	{
		$this->addOutputCode('<div class="'.$this->getAttribute('cssClass').'" id="'.$this->getId().'">');
	}

	function render_html_onEnd()
	{
		$this->addOutputCode('</div>');
	}
}
