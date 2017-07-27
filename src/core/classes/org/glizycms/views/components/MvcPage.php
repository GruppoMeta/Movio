<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizycms_views_components_MvcPage extends org_glizycms_views_components_Page
{
	private $controllerBasePath;
	private $sessionEx;
	private $action;
	private $actionName;

	/**
	 * Init
	 *
	 * @return	void
	 * @access	public
	 */
	function init()
	{
		$this->acceptOutput = true;
		$this->overrideEditableRegion = false;
		$this->canCallController = false;

		// define the custom attributes
		//$this->defineAttribute('actionName',	false, 'action', COMPONENT_TYPE_STRING);
		$this->defineAttribute('defaultAction',	false, 'index', COMPONENT_TYPE_STRING);
		$this->defineAttribute('baseClassPath',	false, '', COMPONENT_TYPE_STRING);
		$this->defineAttribute('controllerName',	false, '', COMPONENT_TYPE_STRING);

		// call the superclass for validate the attributes
		parent::init();

		$this->actionName = $this->getAttribute( 'actionName' );
		if (__Request::get($this->actionName, '') == '') {
			__Request::set($this->actionName, $this->getAttribute( 'defaultAction' ));
		}
	}


	/**
	 * Process
	 *
	 * @return	boolean	false if the process is aborted
	 * @access	public
	 */
	function process()
	{
		if (!$this->_application->canViewPage() || !$this->checkAcl()) {
			org_glizy_helpers_Navigation::accessDenied();
		}

		$this->loadContentFromDB();
		$this->loadTemplate();
		$this->sessionEx = org_glizy_ObjectFactory::createObject('org.glizy.SessionEx', $this->getId());

		$this->action = __Request::get( $this->actionName);
		$oldAction 	= $this->sessionEx->get( $this->actionName );

		foreach ( $this->childComponents  as $c )
		{
			if ( is_a( $c, 'org_glizy_mvc_components_State' ) )
			{
				$c->deferredChildCreation();
			}
		}

		$this->callController();
		$this->canCallController = true;
		$this->action = strtolower( $this->action );
		$this->processChilds();

		if ($this->action) {
			$isStateActive = false;
			$numStates = 0;
			foreach ( $this->childComponents  as $c ) {
				if ( is_a( $c, 'org_glizy_mvc_components_State' ) ) {
					$numStates++;
					$isStateActive = $isStateActive || $c->isCurrentState();
				}
			}

			if (!$isStateActive && $numStates) {
				 new org_glizy_Exception(__T('GLZ_ERR_404'), GLZ_E_404);
			}
		}

		$this->sessionEx->set( $this->actionName, $oldAction );
	}



	/**
	 * Render
	 *
	 * @return	void
	 * @access	public
	 */
	function render($outputMode=NULL, $skipChilds=false)
	{
		$this->_application->_rootComponent->addOutputCode( org_glizy_helpers_JS::JScode( 'if (typeof(Glizy)!=\'object\') Glizy = {}; Glizy.baseUrl ="'.GLZ_HOST.'"; Glizy.ajaxUrl = "ajax.php?pageId='.$this->_application->getPageId().'&ajaxTarget='.$this->getId().'&action=";' ), 'head' );
		return parent::render($outputMode, $skipChilds);
	}



	function getAction()
	{
		return $this->action;
	}


	function getState()
	{
		return strtolower($this->action);
	}

	// TODO
	// modificare il tipo di implementazione
	// non mi piace che un componente chieda l'url ad un'altro componente
	function changeStateUrl($newState='', $amp=false )
	{
		if ( is_null( $this->getAttribute('targetPage') ) )
		{
			return org_glizy_helpers_Link::makeUrl('linkChangeAction', array('pageId' => $this->_application->getPageId(), $this->actionName => $newState));
		}
		else
		{
			return org_glizy_helpers_Link::makeUrl('link', array('pageId' => $this->getAttribute('targetPage')), array($this->actionName => $newState));
		}
	}
}
