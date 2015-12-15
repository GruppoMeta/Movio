<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizy_mvc_components_State extends org_glizy_components_ComponentContainer
{
	private $actions;
	private $actionsOriginal;

	/**
	 * Init
	 *
	 * @return	void
	 * @access	public
	 */
	function init()
	{
		// define the custom attributes
		$this->defineAttribute('name', true, '', COMPONENT_TYPE_STRING);
		$this->defineAttribute('label', false, '', COMPONENT_TYPE_STRING);
		$this->defineAttribute('url', false, NULL, COMPONENT_TYPE_STRING);
		$this->defineAttribute('draw', false , true, COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('backUrl', false , false, COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('cssClassTab', false, '', COMPONENT_TYPE_STRING);

		// call the superclass for validate the attributes
		parent::init();

		$this->actionsOriginal = explode(',', $this->getAttribute('name') );
		$this->actions = explode(',', strtolower($this->getAttribute('name')));
	}

	function deferredChildCreation($force=false)
	{
		if ( $this->isCurrentState() || $force)
		{
			// aggiunge i figli
			if ( !count( $this->childComponents ) )
			{
				$function = 'addChild_'.$this->getId();
				$function ( $this->_application, $this );
				$this->initChilds();
				$this->execDoLater();
			}

			if ( $this->getAttribute( 'backUrl' ) )
			{
				__Session::set( '__backUrl__', __Request::get( '__backUrl__' ) );
			}
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
		if ( $this->isCurrentState() )
		{
			if ( !count( $this->childComponents ) )
			{
				$this->deferredChildCreation();
			}

			$this->processChilds();
		}
	}

	/**
	 * Render
	 *
	 * @return	void
	 * @access	public
	 */
	function render($outputMode=NULL, $skipChilds=false)
	{
		if ( $this->isCurrentState() )
		{
			$cssClass = $this->getAttribute( 'cssClass' );
			if ( !empty( $cssClass ) )
		{
				$this->addOutputCode( '<div class="'.$cssClass.'">' );
			}

			$this->renderChilds($outputMode);

			if ( !empty( $cssClass ) )
			{
				$this->addOutputCode( '</div>' );
			}
		}
	}

	function getAction()
	{
		if ( method_exists( $this->_parent, "getAction" ) )
		{
			return $this->_parent->getAction();
		}
		else if ( method_exists( $this->_parent, "getState" ) )
		{
			return $this->_parent->getState();
		}
		return '';
	}

	function getStateAction()
	{
		return $this->actionsOriginal[0];
	}

	function getActionArray()
	{
		return $this->actions;
	}

	function isCurrentState()
	{
		return in_array( strtolower( $this->getAction() ), $this->actions );
	}

	public static function compile($compiler, &$node, &$registredNameSpaces, &$counter, $parent='NULL', $idPrefix, $componentClassInfo, $componentId)
	{
		$compiler->_classSource .= '$n'.$counter.' = &org_glizy_ObjectFactory::createComponent(\''.$componentClassInfo['classPath'].'\', $application, '.$parent.', \''.$node->nodeName.'\', '.$idPrefix.'\''.$componentId.'\', \''.$componentId.'\', $skipImport)'.GLZ_COMPILER_NEWLINE;

		$forceChildCreation = $node->hasAttribute( 'forceChildCreation' ) && strtolower( $node->getAttribute( 'forceChildCreation' ) ) == 'true' ?
									'true' :
									$node->parentNode->hasAttribute( 'forceChildCreation' ) && strtolower($node->parentNode->getAttribute( 'forceChildCreation' ) ) == 'true' ? 'true' : 'false';
		$compiler->_classSource .= '$forceChildCreation = '.$forceChildCreation.GLZ_COMPILER_NEWLINE;
		if ($parent!='NULL')
		{
			$compiler->_classSource .= $parent.'->addChild($n'.$counter.')'.GLZ_COMPILER_NEWLINE;
		}

		if (count($node->attributes))
		{
			// compila  gli attributi
			$compiler->_classSource .= '$attributes = array(';
			foreach ( $node->attributes as $index=>$attr )
			{
				if ($attr->name!='id')
				{
					$compiler->_classSource .= '\''.$attr->name.'\' => \''.addslashes($attr->value).'\', ';
				}
			}
			$compiler->_classSource .= ')'.GLZ_COMPILER_NEWLINE;
			$compiler->_classSource .= '$n'.$counter.'->setAttributes( $attributes )'.GLZ_COMPILER_NEWLINE;
		}

		$compiler->_classSource .= 'if ($skipImport || $forceChildCreation) {addChild_'.$componentId.'($application, $n'.$counter.', $skipImport, $idPrefix, $mode);}'.GLZ_COMPILER_NEWLINE;

		$previusClassSource = $compiler->_classSource;
		$compiler->_classSource = '';
		$compiler->_classSource .= '// STATE function '.GLZ_COMPILER_NEWLINE2;
		$compiler->_classSource .= 'function addChild_'.$componentId.'( &$application, &$n'.$counter.', $skipImport=false, $idPrefix=\'\', $mode=\'\') {'.GLZ_COMPILER_NEWLINE2;

		$oldcounter = $counter;
		foreach( $node->childNodes as $nc )
		{
			$counter++;
			$compiler->_compileXml($nc, $registredNameSpaces, $counter, '$n'.$oldcounter, $idPrefix);
		}
		$compiler->_classSource .= '} '.GLZ_COMPILER_NEWLINE;
		$compiler->_classSource .= '// end STATE function '.GLZ_COMPILER_NEWLINE2;

		$compiler->_customClassSource .= $compiler->_classSource;
		$compiler->_classSource = $previusClassSource;
	}
}