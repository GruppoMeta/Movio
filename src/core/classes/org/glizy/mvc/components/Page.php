<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizy_mvc_components_Page extends org_glizy_components_Page
{
    protected $controllerBasePath;
    protected $sessionEx;
	protected $action;
	protected $actionName;

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

		// backward compatibility
		$baseClassPath = $this->getAttribute('baseClassPath');
		if ($baseClassPath) {
			$controllerName = $this->getAttribute('controllerName');
			$this->setAttribute('controllerName', $baseClassPath.'.controllers.'.$controllerName.'.*');
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
		$acl = $this->getAttribute( 'acl' );
		if ( !empty( $acl ) )
		{
			list( $service, $action ) = explode( ',', $acl );
			if ( !$this->_user->acl( $service, $action ) )
			{
				$this->breakCycle();
				$this->addOutputCode( "403 Forbidden" );
				return;
			}
		}

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
		parent::process();

		$this->sessionEx->set( $this->actionName, $oldAction );
	}


	/**
	 * Render
	 *
	 * @return	string
	 * @access	public
	 */
	function render($outputMode=NULL, $skipChilds=false)
	{
		$this->_application->_rootComponent->addOutputCode( org_glizy_helpers_JS::JScode( 'if ( typeof(Glizy) == "undefined" ) Glizy = {}; Glizy.baseUrl ="'.GLZ_HOST.'"; Glizy.ajaxUrl = "ajax.php?pageId='.$this->_application->getPageId().'&ajaxTarget='.$this->getId().'&action=";' ), 'head' );
		$t = '';
		$this->applyOutputFilters('pre', $t);
		$this->renderChilds($outputMode);
		return $this->_render();
	}

	function _render()
	{
		if ( $this->getAttribute( 'addCoreJS' ) === true )
		{
			$this->_application->addJSLibCore();
		}
		$template = NULL;

		// riordina l'array con i dati dell'editableRegions da passare alla classe template
		$templateOutput = array();
		$atEnd = false;
		for ($j=0; $j<=1; $j++)
		{
			for ($i=0; $i<count($this->_output); $i++)
			{
				if ($this->_output[$i]['atEnd']===($j==0 ? false : true))
				{
					if (!array_key_exists($this->_output[$i]['editableRegion'], $templateOutput))
					{
						$templateOutput[$this->_output[$i]['editableRegion']] = '';
					}

					$templateOutput[$this->_output[$i]['editableRegion']] .= $this->_output[$i]['code'];
				}
				if ($this->_output[$i]['atEnd']===true) $atEnd = true;
			}
			if (!$atEnd) break;
		}

		$templateFileName = $this->getAttribute( 'templateFileName' );
		if ( empty( $templateFileName ) )
		{
			$templateFileName = $this->_application->getPageId().'.php';
		}
		$template = org_glizy_ObjectFactory::createObject ('org.glizy.template.layoutManager.PHP', $templateFileName );
		$output = $template->apply($templateOutput);
		return $output ;
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