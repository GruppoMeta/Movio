<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_components_Fieldset extends org_glizy_components_ComponentContainer
{
	function __construct(&$application, &$parent, $tagName='', $id='', $originalId='')
	{
		parent::__construct($application, $parent, $tagName, $id, $originalId);
		$this->canHaveChilds	= true;
		$this->overrideEditableRegion 	= false;
	}


	/**
	 * Init
	 *
	 * @return	void
	 * @access	public
	 */
	function init()
	{
		// define the custom attributes
		$this->defineAttribute('label',	false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('cssClass',	false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('addExtraSpan',	false, 	false,	COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('legendTag',	false, 	'legend',	COMPONENT_TYPE_STRING);

		// call the superclass for validate the attributes
		parent::init();
	}

	function render_html_onStart()
	{
		$attributes 		 	= array();
		$attributes['id']		= $this->getOriginalId();
		$attributes['class'] 	= $this->getAttribute('cssClass');
		$output = '<fieldset '.$this->_renderAttributes($attributes).'>';
		if ( !is_null( $this->getAttribute( 'label' ) ) )
		{
            $required = false;
            $data = $this->getAttribute('data');
            if ($data) {
                if (preg_match('/minRec=(\d+)|repeatMin=(\d+)/', $data, $m)) {
                    $required = $m[1] > 0 || $m[2] > 0;
                }
            }
            // TODO usare classe per css
			$output .= '<'.$this->getAttribute( 'legendTag' ).' '.($required ? '' : 'style="font-weight:normal"').'>'.( $this->getAttribute( 'addExtraSpan' ) ? '<span>' : '' ).$this->getAttribute( 'label' ).( $this->getAttribute( 'addExtraSpan' ) ? '</span>' : '' ).'</'.$this->getAttribute( 'legendTag' ).'>';
        }
		$this->addOutputCode($output);
	}

	function render_html_onEnd()
	{
		$this->addOutputCode( '</fieldset>' );
	}


	function getContent()
	{
		return $this->getChildContent();
	}

}
