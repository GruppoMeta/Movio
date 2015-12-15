<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_components_Button extends org_glizy_components_HtmlComponent
{
	var $_targetForm;
	var $_action;
	var $_name;

	/**
	 * Init
	 *
	 * @return	void
	 * @access	public
	 */
	function init()
	{
		// define the custom attributes
		$this->defineAttribute('action',	false, 	'',			COMPONENT_TYPE_STRING);
		$this->defineAttribute('actionType',false, 	'command',	COMPONENT_TYPE_STRING);
		$this->defineAttribute('cssClass',	false, 	'',			COMPONENT_TYPE_STRING);
		$this->defineAttribute('confirmMessage',	false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('disabled',	false, 	false,		COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('value',		true, 	'',			COMPONENT_TYPE_STRING);
		$this->defineAttribute('target',	false, 	NULL,		COMPONENT_TYPE_OBJECT);
		$this->defineAttribute('type',		false, 	'button',	COMPONENT_TYPE_STRING);
		$this->defineAttribute('url',		false, 	'',			COMPONENT_TYPE_STRING);

		// call the superclass for validate the attributes
		parent::init();
	}

	function process()
	{
		$this->_action 	= $this->getAttribute('action');
		$url 			= $this->getAttribute('url');
		$this->_name 	= $this->getId();
		if (!empty($this->_action))
		{
			$this->_targetForm = $this->getAttribute('target');
			if (!is_null($this->_targetForm))
			{
				switch ($this->getAttribute('actionType'))
				{
					case 'location':
						$this->_action = 'location.href='.$this->_targetForm->getJSAction($this->_action);
						break;
					case 'action':
						$this->_action = 'this.form.action='.$this->_targetForm->getJSAction($this->_action);
						break;
					case 'command':
						$this->_action = $this->_targetForm->getJSAction($this->_action);
						break;
					case 'url':
						$this->_action = 'location.href='.$this->_targetForm->getJSAction($this->_action, true);
						break;
					//case 'submit':
					//	$this->_name = $this->_targetForm->getId().'_command';
				}
			}
		}
		else if (!empty($url))
		{
			$this->_action = 'location.href=\''.$this->getAttribute('url').'\'';
		}

		if (!is_null($this->getAttribute('confirmMessage')))
		{
			$this->_action = 'if (confirm(\''.$this->getAttribute('confirmMessage').'\')) '.$this->_action.'; else return false;';
		}
	}

	function render()
	{
		$this->applyOutputFilters('pre', $this->_content);

		if ($this->getAttribute('type')=='reset' && $this->getAttribute('actionType') != 'location' ) $this->setAttribute('type', 'submit');
		$attributes 				= array();
		$attributes['id'] 			= $this->getId();
		$attributes['name'] 		= $this->_name;
		$attributes['disabled'] 	= $this->getAttribute('disabled') ? 'disabled' : '';
		$attributes['class'] 		= $this->getAttribute('cssClass');
		$attributes['type'] 		= $this->getAttribute('type');
		$attributes['value'] 		= $this->encodeOuput($this->getAttribute('value'));
		$attributes['onclick'] 		= $this->_action;

		$output = '<input '.$this->_renderAttributes($attributes).' />';
		$this->addOutputCode($output);
	}
}