<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


define('GLZ_STATE_SWITCH_SUFFIX', 'state');

class org_glizy_components_StateSwitch extends org_glizy_components_ComponentContainer
{
	var $_currentState 	= NULL;
	var $_sessionEx		= NULL;
	var $_oldState		= NULL;
	var $_stateName		= NULL;

	/**
	 * Init
	 *
	 * @return	void
	 * @access	public
	 */
	function init()
	{
		// define the custom attributes
		$this->defineAttribute('customClass',		false, '', 		COMPONENT_TYPE_STRING);
		$this->defineAttribute('defaultState',		false, NULL, 	COMPONENT_TYPE_STRING);
		$this->defineAttribute('rememberState',		false, true, 	COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('useIdPrefix',		false, false, 	COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('overrideEditableRegion',		false, true, 	COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('targetPage',	false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('forceChildCreation',	false, 	NULL,	COMPONENT_TYPE_BOOLEAN);

		// call the superclass for validate the attributes
		parent::init();
	}

	/**
	 * Process
	 *
	 * @return	boolean	false if the process is aborted
	 * @access	public
	 */
	function process()
	{
		$this->overrideEditableRegion = $this->getAttribute('overrideEditableRegion');
		$this->_stateName 	= $this->getAttribute('useIdPrefix') ? $this->getId().'_'.GLZ_STATE_SWITCH_SUFFIX : GLZ_STATE_SWITCH_SUFFIX;
		$this->_sessionEx	= org_glizy_ObjectFactory::createObject('org.glizy.SessionEx', $this->getId());

		$this->_oldState 	= $this->_sessionEx->get($this->_stateName);

		// se lo stato non è setttato lo cerca nella sessione
		if ($this->getAttribute('rememberState'))
		{
			$this->_currentState = $this->_sessionEx->get($this->_stateName);
		}

		if (is_null($this->_currentState))
		{
			$this->resetState();
		}

		$newState = org_glizy_Request::get($this->_stateName, NULL);
		if (!empty($newState))
		{
			// cambio di stato
			// TODO
			// verificare che lo stato impostato sia definito
			$this->_currentState = org_glizy_Request::get($this->_stateName, NULL);
		}

		if ($this->_currentState=='reset')
		{
			$this->_currentState = NULL;
			$this->resetState();
		}
		$this->_currentState = strtolower($this->_currentState);
		$this->_sessionEx->set($this->_stateName, $this->_currentState);

		$customClassName = $this->getAttribute('customClass');
		if (!empty($customClassName))
		{
			$customClass = &org_glizy_ObjectFactory::createObject($customClassName, $this);
			// TODO
			// createObject purtroppo non passa i parametri in riferimento e questa è una grande limitazione
			$customClass->_parent = &$this;
			if (method_exists($customClass, $this->_currentState))  call_user_func(array($customClass, $this->_currentState), $this->_oldState);
			else if (method_exists($customClass, 'execute_'.$this->_currentState))  call_user_func(array($customClass, 'execute_'.$this->_currentState), $this->_oldState);
		}
		else
		{
			if (method_exists($this, $this->_currentState))  call_user_func(array($this, $this->_currentState), $this->_oldState);
		}

		$this->processChilds();

		if (!empty($customClassName))
		{
			$customClass = &org_glizy_ObjectFactory::createObject($customClassName, $this);
			$customClass->_parent = &$this;
			if (method_exists($customClass, 'executeLater_'.$this->_currentState))  call_user_func(array($customClass, 'executeLater_'.$this->_currentState), $this->_oldState);
		}
	}

/* */

	function getState()
	{
		return strtolower($this->_currentState);
	}

	function setState($value)
	{
		$this->_currentState = $value;
		$this->_sessionEx->set($this->_stateName, $this->_currentState);
	}

	function resetState()
	{
		// se lo stato non è settato dagli attributi
		if (is_null($this->_currentState)) $this->_currentState = $this->getAttribute('defaultState');

		// se lo stato non è settato lo legge dal primo figlio
		if (is_null($this->_currentState)) $this->_currentState = $this->childComponents[0]->getDefaultState();

		//TODO
		// se lo stato è nullo
		// visualizzare un errore
	}

	// TODO
	// modificare il tipo di implementazione
	// non mi piace che un componente chieda l'url ad un'altro componente
	function changeStateUrl($newState='', $amp=false )
	{
		if ( is_null( $this->getAttribute('targetPage') ) )
		{
			return org_glizy_helpers_Link::addParams(array($this->_stateName => $newState));
		}
		else
		{
			return org_glizy_helpers_Link::makeUrl('link', array('pageId' => $this->getAttribute('targetPage')), array($this->_stateName => $newState));
		}
	}

	function refreshToState($newState='', $params=null)
	{
		$url = str_replace( '&amp;', '&', $this->changeStateUrl( $newState ) );
		org_glizy_helpers_Navigation::gotoUrl( $url, $params);
	}


	function getStateParamName()
	{
		return $this->_stateName;
	}

	// TODO
	// modificare il tipo di implementazione
	// non mi piace che un componente chieda l'url ad un'altro componente
	//
	// TODO
	// la generazione dei link deve sempre passare dal org_glizy_helpers_Link
	function getJSAction($action, $forceUrl=true)
	{
		$pageId = $this->_application->getPageId();
		$targetPage = $this->getAttribute('targetPage');
		if (!is_null($targetPage))
		{
			$pageId = $this->getAttribute('targetPage');
		}

		if ( $forceUrl )
		{
			$url = '\''.org_glizy_helpers_Link::makeUrl('link', array('pageId' => $pageId), array($this->_stateName => $action)).'\'';
		}
		else
		{
			$url = '\''.org_glizy_helpers_Link::addParams( array($this->_stateName => $action) ).'\'';
		}


		return $url;
	}
}