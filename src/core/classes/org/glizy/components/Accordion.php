<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_components_Accordion extends org_glizy_components_ComponentContainer
{
	private static $groupsOpen = array();
	private static $groupsClose = array();
	private $group;

	public function init()
	{
		// define the custom attributes
		$this->defineAttribute('label', true, '', COMPONENT_TYPE_STRING);
		$this->defineAttribute('cssClass', false,  __Config::get('glizy.accordion'), COMPONENT_TYPE_STRING);
		$this->defineAttribute('cssClassHeading', false,  __Config::get('glizy.accordion.heading'), COMPONENT_TYPE_STRING);
		$this->defineAttribute('cssClassTitle', false,  __Config::get('glizy.accordion.title'), COMPONENT_TYPE_STRING);
		$this->defineAttribute('cssClassBodyDiv', false,  __Config::get('glizy.accordion.bodyDiv'), COMPONENT_TYPE_STRING);
		$this->defineAttribute('cssClassBody', false,  __Config::get('glizy.accordion.body'), COMPONENT_TYPE_STRING);
		$this->defineAttribute('cssClassOpen', false,  __Config::get('glizy.accordion.open'), COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('open', false, false, COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('group', false, '', COMPONENT_TYPE_STRING);

		// call the superclass for validate the attributes
		parent::init();
	}

	public function process()
	{
		$id = $this->getId();
		$group = $this->getAttribute('group');
		$this->group = $group ? : $id;
		if (!isset(self::$groupsOpen[$this->group])) {
			self::$groupsOpen[$this->group] = $id;
		}
		self::$groupsClose[$this->group] = $id;
	}

	public function render_html_onStart()
	{
		$id = $this->getId();
		$label = $this->getAttribute('label');
		$cssClass = $this->getAttribute('cssClass');
		$cssClassHeading = $this->getAttribute('cssClassHeading');
		$cssClassTitle = $this->getAttribute('cssClassTitle');
		$cssClassBody = $this->getAttribute('cssClassBody');
		$cssClassBodyDiv = $this->getAttribute('cssClassBodyDiv');
		$open = $this->getAttribute('open') === true ? 'in' : '';
		$groupId = $this->group;

		$output = <<<EOD
<div id="{$id}" class="{$cssClass}">
	<div id="{$id}_heading" class="{$cssClassHeading}" role="tab" >
		<h4 class="{$cssClassTitle}">
			<a href="#{$id}_body" role="button" data-toggle="collapse" data-parent="#{$groupId}" aria-expanded="false" aria-controls="#{$id}_body">{$label}</a>
		</h4>
	</div>
	<div id="{$id}_body" class="{$cssClassBodyDiv} {$open}" role="tabpanel" aria-labelledby="{$id}_heading" aria-expanded="false">
		<div class="{$cssClassBody}">
EOD;

		if (self::$groupsOpen[$this->group]==$id) {
			$output = <<<EOD
<div class="panel-group" id="{$groupId}" role="tablist" aria-multiselectable="true">
{$output}
EOD;
		}

		$this->addOutputCode($output);
	}

	public function render_html_onEnd()
	{
		$output = '</div></div></div>';
		if (self::$groupsClose[$this->group]==$this->getId()) {
			$output .= '</div>';
		}

		$this->addOutputCode($output);
	}
}
