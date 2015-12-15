<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_components_ListItem extends org_glizy_components_Component
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
		$this->defineAttribute('key',			false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('options',		false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('selected',		false, 	false,	COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('value',			true, 	'',		COMPONENT_TYPE_STRING);
		$this->defineAttribute('acl',			false, 	'',		COMPONENT_TYPE_STRING);

		// call the superclass for validate the attributes
		parent::init();
	}


	function getItem()
	{
		$acl = $this->getAttribute('acl');
		if ($acl) {
			list( $service, $action ) = explode( ',', $acl );
			if ( !$this->_user->acl( $service, $action ) )
			{
				return false;
			}
		}
		$key 	= !is_null($this->getAttribute('key')) ? $this->getAttribute('key') : $this->getAttribute('value');
//		$value	= $this->encodeOuput($this->getAttribute('value'));
		$value	= $this->getAttribute('value');
		$options = $this->getAttribute('options');

		return array('key' => $key, 'value' => $value, 'selected' => $this->getAttribute('selected'), 'options' => $options, 'cssClass' =>  $this->getAttribute('cssClass') );
	}
}