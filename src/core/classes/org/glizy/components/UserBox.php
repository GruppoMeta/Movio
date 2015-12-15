<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_components_UserBox extends org_glizy_components_ComponentContainer
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
		$this->defineAttribute('showWelcome',	false, 	true,	COMPONENT_TYPE_BOOLEAN);
		$this->acceptOutput = true;
		parent::init();
	}

	function process()
	{
		$user = &$this->_application->getCurrentUser();
		$this->_content = array();
		$this->_content['id'] = $this->getId();
		$this->_content['cssClass'] = $this->getAttribute('cssClass');
		$this->_content['message'] = '';
		if ( $this->getAttribute('showWelcome') )
		{
			$this->_content['message'] = org_glizy_locale_Locale::get('LOGGED_MESSAGE', $user->firstName);
		}
		$this->processChilds();
	}
}

if (!class_exists('org_glizy_components_UserBox_render'))
{
	class org_glizy_components_UserBox_render extends org_glizy_components_render_Render
	{
		function getDefaultSkin()
		{
			$skin = <<<EOD
<div class="" tal:attributes="class UserBox/cssClass; id UserBox/id">
<h3 tal:condition="UserBox/message" tal:content="structure UserBox/message" />
<span tal:omit-tag="" tal:content="structure childOutput" />
</div>
EOD;
			return $skin;
		}
	}
}