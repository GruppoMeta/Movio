<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_components_Form extends org_glizy_components_ComponentContainer
{
	var $_command = '';
	private $currentRenderChildId;

	/**
	 * Init
	 *
	 * @return	void
	 * @access	public
	 */
	function init()
	{
		// define the custom attributes
		$this->defineAttribute('addValidationJs',	false, 	true,	COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('action',	false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('fieldset', 	false, 	false, 	COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('method',	false, 	'post',	COMPONENT_TYPE_STRING);
		$this->defineAttribute('onsubmit',	false, 	'',		COMPONENT_TYPE_STRING);
		$this->defineAttribute('cssClass', 	false, __Config::get('glizy.form.cssClass'), 		COMPONENT_TYPE_STRING);
		// $this->defineAttribute('command', 	false, '', 		COMPONENT_TYPE_STRING);
		$this->defineAttribute('enctype', 	false, '', 		COMPONENT_TYPE_STRING);
		$this->defineAttribute('removeGetValues', false, true, 		COMPONENT_TYPE_STRING );
		$this->defineAttribute('readOnly',			false, 	false,	COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('dataProvider',	false, 	NULL,	COMPONENT_TYPE_OBJECT);

		// call the superclass for validate the attributes
		parent::init();
	}

	function process()
	{
		if ( $this->getAttribute( 'readOnly' ) )
		{
			$this->applyReadOnlyToAllChild( $this );
		}

		$dp = $this->getAttribute('dataProvider');
		if ( is_object( $dp ) )
		{
			$it = $dp->loadQuery();
			if ( is_object( $it ) )
			{
				$arC = $it->current();
		        if ( is_object( $arC ) )
		        {
		        	__Request::setFromArray( $arC->getValuesAsArray() );
		        }
			}
		}


		$this->_command = org_glizy_Request::get($this->getId().'_command', NULL);
		$this->processChilds();
	}

	function render($outputMode=NULL, $skipChilds=false)
	{
		if ( $this->getAttribute( 'addValidationJs' ) )
		{
			$this->_application->addValidateJsCode( $this->getId() );
		}

		if (!is_null($this->getAttribute('skin')) && $outputMode=='html') {
			$this->acceptOutput = true;
			$this->_content = new StdClass;

			for ($i=0; $i<count($this->childComponents);$i++)
			{
				if ($this->childComponents[$i]->getAttribute('visible') && $this->childComponents[$i]->getAttribute('enabled'))
				{
					$this->currentRenderChildId = $this->childComponents[$i]->getId();
					$this->_content->{$this->currentRenderChildId} = '';
					$this->childComponents[$i]->render($outputMode);
					$this->state = COMPONENT_STATE_RENDER;
					if ($this->checkBreakCycle())
					{
						$this->state = COMPONENT_STATE_BLOCKED;
						$this->breakCycle(false);
						break;
					}
				}
			}
		}

		parent::render($outputMode, $skipChilds);
	}



	function addOutputCode($output, $editableRegion='', $atEnd=false)
	{
		if ($this->acceptOutput)
		{
			$this->_content->{$this->currentRenderChildId} = $output;
		}
		else
		{
			$this->addParentOutputCode($output, $editableRegion, $atEnd);
		}

	}



	function render_html_onStart()
	{
		$attributes 				= array();
		$attributes['id'] 			= $this->getId();

		if ( !is_null($this->getAttribute('action')) )
		{
			$attributes['action'] = $this->getAttribute('action');
		}
		else
		{
			$removeValues = $this->getAttribute('removeGetValues');
			if ( $removeValues === true || $removeValues == 'true' )
			{
				$attributes['action'] = org_glizy_Routing::scriptUrl( true );
			}
			else
			{
				$attributes['action'] = org_glizy_helpers_Link::removeParams( explode( ',', $removeValues ) );
			}
		}
		$attributes['method'] 		= $this->getAttribute('method');
		$attributes['onsubmit'] 	= $this->getAttribute('onsubmit');
		$attributes['class'] 		= $this->getAttribute('cssClass');
		$attributes['enctype'] 		= $this->getAttribute('enctype');

		$output  = '<form '.$this->_renderAttributes($attributes).'>';
		if ($this->getAttribute('fieldset')) $output .= '<fieldset>';
		// $output .= org_glizy_helpers_Html::applyItemTemplate('',
		// 				org_glizy_helpers_Html::hidden($this->getId().'_command', $this->getAttribute('command') ),
		// 				true );
		$this->addOutputCode( $output );
	}

	function render_html_onEnd()
	{
		$output = '';
		if ($this->getAttribute('fieldset')) $output .= '</fieldset>';
		$output  .= '</form>';
		$this->addOutputCode($output);
	}


	function getJSAction($action)
	{
		return 'this.form.'.$this->getId().'_command.value = \''.$action.'\'';
	}

	function getCommadFieldName()
	{
		return $this->getId().'_command';
	}


	function loadContent($name, $bindToField=NULL)
	{
		if (empty($bindToField))
		{
			$bindToField = $name;
		}
		return  org_glizy_Request::get($bindToField, $this->_parent->loadContent($bindToField));
	}

	function applyReadOnlyToAllChild( $node )
	{
		if ($node->canHaveChilds)
		{
			for ($i=0; $i<count($node->childComponents);$i++)
			{
				if ( is_subclass_of( $node->childComponents[$i], 'org_glizy_components_HtmlFormElement' ) )
				{
					$node->childComponents[$i]->setAttribute( 'readOnly', true );
				}
				$this->applyReadOnlyToAllChild( $node->childComponents[$i] );
			}
		}
	}

	function getContent()
	{
		return $this->_content;
	}
}