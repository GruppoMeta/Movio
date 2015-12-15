<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_components_Login extends org_glizy_components_LoginBox
{
	var $_error = NULL;

	/**
	 * Init
	 *
	 * @return	void
	 * @access	public
	 */
	function init()
	{
		$this->defineAttribute('accessPageId',		false, 	NULL,						COMPONENT_TYPE_STRING);
		$this->defineAttribute('allowGroups',		false, 	'',							COMPONENT_TYPE_STRING);
		$this->defineAttribute('backend',			false, 	true,						COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('errorLabel',		false, 	__T('GLZ_LOGIN_ERROR'),		COMPONENT_TYPE_STRING);
		$this->defineAttribute('userField',			false, 	'loginuser', 				COMPONENT_TYPE_STRING);
		$this->defineAttribute('passwordField',		false, 	'loginpsw', 				COMPONENT_TYPE_STRING);
		$this->defineAttribute('rememberField',		false, 	'loginremember', 			COMPONENT_TYPE_STRING);

		parent::init();
	}

	function render_html()
	{
		if (!is_null($this->_content['errorLabel']))
		{
			$this->addOutputCode($this->_content['errorLabel']);
		}
	}
}