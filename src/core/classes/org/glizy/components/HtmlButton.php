<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_components_HtmlButton extends org_glizy_components_Component
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
		$this->defineAttribute('cssClass',	false, 	__Config::get('glizy.formButton.cssClass'),			COMPONENT_TYPE_STRING);
		$this->defineAttribute('bindTo',	false, 	NULL,		COMPONENT_TYPE_STRING);
		$this->defineAttribute('disabled',	false, 	false,		COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('label',		true, 	'',			COMPONENT_TYPE_STRING);
		$this->defineAttribute('name',		false, 	'',			COMPONENT_TYPE_STRING);
		$this->defineAttribute('value',		false, 	'',			COMPONENT_TYPE_STRING);
		$this->defineAttribute('type',  	false,  'submit',   COMPONENT_TYPE_STRING);
		$this->defineAttribute('target',	false, 	NULL,		COMPONENT_TYPE_OBJECT);
		$this->defineAttribute('tag',		false, 	'input',	COMPONENT_TYPE_STRING);
		$this->defineAttribute('onclick',	false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('routeUrl',	false, 	'',		COMPONENT_TYPE_STRING);
		$this->defineAttribute('url',	false, 	'',		COMPONENT_TYPE_STRING);
  		$this->defineAttribute('confirmMessage',	false, 	NULL,	COMPONENT_TYPE_STRING);

		// call the superclass for validate the attributes
		parent::init();
	}

	function process()
	{
		$this->_content = $this->getAttribute('value');
		if (is_object($this->_content))
		{
			$contentSource = &$this->getAttribute('value');
			$this->_content = $contentSource->loadContent($this->getId(), $this->getAttribute('bindTo'));
		}
		else if (is_null($this->_content))
		{
			$this->_content = $this->_parent->loadContent($this->getId(), $this->getAttribute('bindTo'));
		}
		if (empty($this->_content))
		{
			$this->_content = '';
		}

		$targetForm = $this->getAttribute('target');
		if (!is_null($targetForm))
		{
			$this->setAttribute('name', $targetForm->getCommadFieldName());
		}
	}

	function render()
	{
		$this->applyOutputFilters('pre', $this->_content);
		$output = '';

		$attributes 				= array();
		$attributes['id'] 			= $this->getId();
		// $attributes['name'] 		= $this->getAttribute('name') != '' ? $this->getAttribute('name') : $this->getOriginalId();
		$attributes['name'] 		= $this->getOriginalId();
		$attributes['disabled'] 	= $this->getAttribute('disabled') ? 'disabled' : '';
		$attributes['class'] 		= $this->getAttribute('cssClass');
		$attributes['type'] 		= $this->getAttribute('type');
		$attributes['onclick'] 		= $this->getAttribute('onclick');

		$routeUrl = $this->getAttribute( 'routeUrl' );
		if ( $routeUrl != '' )
		{
			$attributes['onclick'] = 'location.href=\''.__Link::makeUrl( $routeUrl ).'\'';

			if (!is_null($this->getAttribute('confirmMessage')))
			{
				$attributes['onclick'] =  'if (confirm(\''.$this->getAttribute('confirmMessage').'\')) '.$attributes['onclick'].'; else return false;';
			}
		}
		$url = $this->getAttribute( 'url' );
		if ( $url != '' )
		{
			$attributes['onclick'] = 'location.href=\''.$url.'\'';
		}

		$targetForm = $this->getAttribute('target');
		if (!is_null($targetForm))
		{
			$this->addTranslateInfo( $targetForm->getCommadFieldName(), $this->getAttribute('label'), $this->getAttribute('value') );
		}
		else
		{
			$this->addTranslateInfo( $this->getAttribute('name'), $this->getAttribute('label'), $this->getAttribute('value') );
		}

		if ($this->getAttribute('tag') == 'input')
		{
			$attributes['value'] = glz_encodeOutput($this->getAttribute('label'));
			$output .= '<input '.$this->_renderAttributes($attributes).' />';
		}
		else
		{
			$attributes['value'] = glz_encodeOutput($this->getAttribute('value'));
			if ( strpos( $attributes['value'], 'route:' ) === 0 )
			{
				$attributes['value'] = __Link::makeUrl( str_replace( 'route:', '', $attributes['value'] ) );
			}
			$output .= '<button '.$this->_renderAttributes($attributes).'>'.$this->getAttributeString('label').'</button>';
		}

		$this->addOutputCode($output);
	}

	private function addTranslateInfo( $target, $label, $buttonValue )
	{
		if ($target) {
			$infoName = '__translateInfo_'.strtolower( $this->_application->getPageId() );
			$translateInfo = __Session::get( $infoName, array( ) );
			$newTranslateInfo = array();
			foreach ( $translateInfo as $value )
			{
				if ( $value[ 'target_name' ] != $this->getOriginalId() )
				{
					$newTranslateInfo[] = $value;
				}
			}
			$newTranslateInfo[] = array( 'target_name' => $this->getOriginalId(), 'target' => $target, 'label' => $label, 'value' => $buttonValue );
			__Session::set( $infoName, $newTranslateInfo );
		}
	}
}