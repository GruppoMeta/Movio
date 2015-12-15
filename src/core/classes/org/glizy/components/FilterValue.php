<?php
class org_glizy_components_FilterValue extends org_glizy_components_Component
{
	/**
	 * Init
	 *
	 * @return	void
	 * @access	public
	 */
	function init()
	{
		// define the custom attributes
		$this->defineAttribute('name',			true, 	'',		COMPONENT_TYPE_STRING);
		$this->defineAttribute('value',			true, 	'',		COMPONENT_TYPE_STRING);

		// call the superclass for validate the attributes
		parent::init();
	}


	function getItem()
	{
		return array($this->getAttribute('name') => $this->getAttribute('value'));
	}
}