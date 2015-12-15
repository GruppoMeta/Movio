<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizy_components_LoginCheck extends org_glizy_components_Component
{

	/**
	 * Init
	 *
	 * @return	void
	 * @access	public
	 */
	function init()
	{
		$this->defineAttribute('cssClass',		false, 	'',		COMPONENT_TYPE_STRING);
		$this->defineAttribute('text',			false, 	'',		COMPONENT_TYPE_STRING);
		$this->defineAttribute('allowGroups',   false,  '',		COMPONENT_TYPE_STRING);

		parent::init();
	}

	function process()
	{
		$user = &$this->_application->getCurrentUser();

		$allowGroups = $this->getAttribute('allowGroups')!='' ? explode(',', $this->getAttribute('allowGroups')) : array();

		if (!$user->isLogged() || !(count($allowGroups) ? in_array($user->groupId, $allowGroups) : true))
		{
			$this->breakCycle();
		}
	}

	function render()
	{
		$user = &$this->_application->getCurrentUser();
		$allowGroups = $this->getAttribute('allowGroups')!='' ? explode(',', $this->getAttribute('allowGroups')) : array();
		if (!$user->isLogged() || !(count($allowGroups) ? in_array($user->groupId, $allowGroups) : true))
		{
			$this->breakCycle();
			$output = '<div'.($this->getAttribute('cssClass')!='' ? ' class="'.$this->getAttribute('cssClass').'"' : '').'>'.$this->getAttribute('text').'</div>';
			$this->addOutputCode($output);
		}
	}
}