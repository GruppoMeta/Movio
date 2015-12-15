<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_components_Groupbox extends org_glizy_components_ComponentContainer
{
	var $_caption = NULL;

	/**
	 * Init
	 *
	 * @return	void
	 * @access	public
	 */
	function init()
	{
		// define the custom attributes
		$this->defineAttribute('label',		false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('onlyAdmin',		false, 	false,	COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('cssClass',		false, 	'',	COMPONENT_TYPE_STRING);

		// call the superclass for validate the attributes
		parent::init();

	}

	// TODO
	// dove è usato questo metodo?
	function getChildsInfo(&$result)
	{
		for ($i=0; $i<count($this->childComponents);$i++)
		{
			$result[] = array(	'id' => $this->childComponents[$i]->getId(),
								'originalId' => $this->childComponents[$i]->getOriginalId(),
								'className' => get_class($this->childComponents[$i]),
								'parent' => $this->getId());
			if (method_exists($this->childComponents[$i], 'getChildsInfo'))
			{
				$this->childComponents[$i]->getChildsInfo($result);
			}
		}
	}

	function getContent()
	{
		return is_array( $this->_content ) ? array_merge( $this->_content, $this->getChildContent() ) : $this->getChildContent();
	}


	function render_html_onStart()
	{
		if (!$this->getAttribute('onlyAdmin'))
		{
			$attributes			= array();
			$attributes['id'] 	= $this->getId();
			$attributes['class'] = $this->getAttribute('cssClass');

			$output  = '<fieldset '.$this->_renderAttributes($attributes).'>';
			if (!is_null($this->getAttribute('label'))) $output  .= '<legend>'.$this->getAttribute('label').'</legend>';
			$this->addOutputCode($output);
		}
	}

	function render_html_onEnd()
	{
		if (!$this->getAttribute('onlyAdmin'))
		{
			$output  = '</fieldset>';
			$this->addOutputCode($output);
		}
	}

	public static function translateForMode_edit($node) {

        $attributes = array();
        $attributes['id'] = $node->getAttribute('id');
        $attributes['label'] = $node->getAttribute('label');
        $attributes['cssClass'] = $node->getAttribute('cssClass');

        return org_glizy_helpers_Html::renderTag('glz:Panel', $attributes);
    }

}
