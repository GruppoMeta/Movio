<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

/**
 * Class org_glizy_components_LoginBox
 */
class org_glizy_components_LoginBox extends org_glizy_components_Component
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
		$this->defineAttribute('askPasswordLabel',	false, 	'',							COMPONENT_TYPE_STRING);
		$this->defineAttribute('askPasswordUrl',	false, 	'',							COMPONENT_TYPE_STRING);
		$this->defineAttribute('backend',			false, 	false,						COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('confirmLabel',		false, 	'',							COMPONENT_TYPE_STRING);
		$this->defineAttribute('cssClass',			false, 	'',							COMPONENT_TYPE_STRING);
		$this->defineAttribute('errorLabel',		false, 	__T('GLZ_LOGIN_ERROR'),		COMPONENT_TYPE_STRING);
		$this->defineAttribute('userLabel',			false, 	__T('GLZ_USER_LOGINID'), 	COMPONENT_TYPE_STRING);
		$this->defineAttribute('userField',			false, 	'loginuser', 				COMPONENT_TYPE_STRING);
		$this->defineAttribute('passwordLabel',		false, 	__T('GLZ_USER_PASSWORD'), 	COMPONENT_TYPE_STRING);
		$this->defineAttribute('passwordField',		false, 	'loginpsw', 				COMPONENT_TYPE_STRING);
		$this->defineAttribute('languageLabel',		false, 	__T('GLZ_LANGUAGE'), 		COMPONENT_TYPE_STRING);
		$this->defineAttribute('languageField',		false, 	'loginlanguage',			COMPONENT_TYPE_STRING);
		$this->defineAttribute('registrationUrl',	false, 	'', 						COMPONENT_TYPE_STRING);
		$this->defineAttribute('registrationLabel',	false, 	'', 						COMPONENT_TYPE_STRING);
		$this->defineAttribute('rememberLabel',		false, 	__T('GLZ_LOGIN_REMEMBER'), 	COMPONENT_TYPE_STRING);
		$this->defineAttribute('rememberField',		false, 	'remember', 				COMPONENT_TYPE_STRING);
		$this->defineAttribute('title',				false, 	'', 						COMPONENT_TYPE_STRING);

		parent::init();
	}

	function process()
	{
		$this->_content = array();
		$this->_content['errorLabel'] = '';

		// check if the user is already logged
		$backend = $this->getAttribute('backend');
		$allowGroups = $this->getAttribute('allowGroups')!='' ? explode(',', $this->getAttribute('allowGroups')) : array();
		if ($this->_user->isLogged()) {
			if (($backend && !$this->_user->backEndAccess) ||
				(count($allowGroups) && !in_array($this->_user->groupId, $allowGroups))) {
				$this->_content['errorLabel'] = org_glizy_locale_Locale::get('LOGGER_INSUFFICIENT_GROUP_LEVEL');
			} else {
			$this->setAttribute('visible', false);
			$this->redirectAfterLogin();
		}
		}

		$submitId = 'submit_'.$this->getId();
		$this->_content['id'] 					= $this->getId();
		$this->_content['submitName'] 			= $submitId;
		$this->_content['cssClass'] 			= $this->getAttribute('cssClass');
		$this->_content['userLabel'] 			= $this->getAttribute('userLabel');
		$this->_content['userField'] 			= $this->getAttribute('userField');
		$this->_content['passwordLabel'] 		= $this->getAttribute('passwordLabel');
		$this->_content['passwordField'] 		= $this->getAttribute('passwordField');
		$this->_content['registrationPage'] 	= $this->getAttribute('registrationPage');
		$this->_content['registrationLabel'] 	= $this->getAttribute('registrationLabel');
		$this->_content['confirmLabel'] 		= $this->getAttribute('confirmLabel');
		$this->_content['rememberLabel'] 		= $this->getAttribute('rememberLabel');
		$this->_content['askPasswordLabel'] 	= $this->getAttribute('askPasswordLabel');
		$this->_content['title'] 				= $this->getAttributeString('title');
		$this->_content['__url__'] 				= org_glizy_helpers_Link::makeURL($this->getAttribute('registrationUrl'));
		$this->_content['askPasswordUrl'] 		= org_glizy_helpers_Link::makeURL($this->getAttribute('askPasswordUrl'));

		if (__Request::exists($this->_content['userField']) || __Request::exists($this->_content['passwordField'])) {
			$authClass = org_glizy_ObjectFactory::createObject(__Config::get('glizy.authentication'));
			if ($authClass) {
				try {
					$authClass->setAllowGroups($allowGroups);
					$authClass->setOnlyBackendUser($backend);
					$authClass->setUserLanguage(__Request::get($this->getAttribute('languageField')));
					$authClass->loginFromRequest($this->getAttribute('userField'), $this->getAttribute('passwordField'), $this->getAttribute('rememberField'), true);
					$this->redirectAfterLogin();

				} catch(org_glizy_authentication_AuthenticationException $e) {
					switch ($e->getCode()) {
						case org_glizy_authentication_AuthenticationException::EMPTY_LOGINID_OR_PASSWORD:
						case org_glizy_authentication_AuthenticationException::WRONG_LOGINID_OR_PASSWORD:
							$this->_content['errorLabel'] = $this->getAttribute('errorLabel');
							break;
						case org_glizy_authentication_AuthenticationException::USER_NOT_ACTIVE:
							$this->_content['errorLabel'] = org_glizy_locale_Locale::get('GLZ_LOGIN_DISABLED');
							break;
						case org_glizy_authentication_AuthenticationException::ACCESS_NOT_ALLOWED:
							$this->_content['errorLabel'] = org_glizy_locale_Locale::get('LOGGER_INSUFFICIENT_GROUP_LEVEL');
							break;
					}
				}
			} else {
				// TODO mostrare errore
				$this->_content['errorLabel'] = __Config::get('glizy.authentication');
			}
		} else {
			if (!$this->_content['errorLabel']) {
			$this->_content['errorLabel'] = org_glizy_Session::get('glizy.loginError', '');
			org_glizy_Session::remove('glizy.loginError');
			}
		}
	}

	private function redirectAfterLogin()
	{
		$destPage = '';
		$accessPageId = $this->getAttribute('accessPageId');
		if ( $accessPageId && $accessPageId != $this->_application->getPageId() ) {
			$destPage = strpos($accessPageId, 'http')!==false ? $accessPageId : org_glizy_helpers_Link::makeUrl('link', array('pageId' => $this->getAttribute('accessPageId')));
		}
		$url = 	org_glizy_Session::get('glizy.loginUrl', $destPage);
		if ($url) {
			org_glizy_Session::remove('glizy.loginUrl' );
			org_glizy_helpers_Navigation::gotoUrl($url);
		}
	}
}

if (!class_exists('org_glizy_components_LoginBox_render'))
{
	class org_glizy_components_LoginBox_render extends org_glizy_components_render_Render
	{
		function getDefaultSkin()
		{
			$skin = <<<EOD
<div tal:attributes="id Component/id; class Component/cssClass">
	<h3 tal:content="Component/title" />
	<div>
		<form id="" method="post" action="" tal:attributes="id Component/id">
			<p class="error" tal:condition="Component/errorLabel" tal:content="structure Component/errorLabel"></p>
			<label tal:attributes="for Component/userField" tal:content="structure Component/userLabel" /><br />
			<input type="text" class="text" tal:attributes="id Component/userField; name Component/userField"/><br />
			<label tal:attributes="for Component/passwordField" tal:content="structure Component/passwordLabel" /><br />
			<input type="password" class="text" tal:attributes="id Component/passwordField; name Component/passwordField"/><br />
			<table>
				<tr>
					<td><input name="remember" id="remember" value="1" type="checkbox" /><label  for="remember" tal:content="structure Component/rememberLabel" /></td>
					<td class="submitButton"><input type="submit" class="submitButton" tal:attributes="name Component/submitName;value Component/confirmLabel"/></td>
				</tr>
			</table>
			<a class="link" tal:attributes="href Component/__url__" tal:content="structure Component/registrationLabel"></a><br />
			<a class="link" tal:attributes="href Component/askPasswordUrl" tal:content="structure Component/askPasswordLabel"></a>
		</form>
	</div>
</div>
EOD;
			return $skin;
		}
	}
}
