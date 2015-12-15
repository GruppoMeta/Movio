<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_components_LongText extends org_glizy_components_Text
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
		$this->defineAttribute('label',					false, 	'',		COMPONENT_TYPE_STRING);
		$this->defineAttribute('forceP',				false, 	false,	COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('paginate',				false, 	NULL,	COMPONENT_TYPE_OBJECT);
        $this->defineAttribute('parseInternalLinks',    false,  true,   COMPONENT_TYPE_BOOLEAN);

		$this->defineAttribute('adm:rows',				false, 	'',		COMPONENT_TYPE_STRING);
		$this->defineAttribute('adm:cols',				false, 	'',		COMPONENT_TYPE_STRING);
		$this->defineAttribute('adm:disabled',			false, 	false,	COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('adm:htmlEditor',		false, 	false,	COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('adm:maxLength',			false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('adm:required',			false, 	false,	COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('adm:tinyMCEplugin',		false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('adm:validationType',		false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('adm:validationMessage',		false, 	NULL,	COMPONENT_TYPE_STRING);

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
		parent::process();
		if ($this->getAttribute('adm:htmlEditor')!==true)
		{
			//$tagContent = strip_tags($this->getText());
			$tagContent = $this->getText();
			$this->setText($tagContent);
		}
		else
		{
			$tagContent = trim($this->getText());
			$tagContent = str_replace(array("\r\n", "\n", "\r"), "", $tagContent);
			$this->setText($tagContent);
		}
	}

	function render_html()
	{
		$this->addOutputCode($this->getContent());
	}

    /**
     * @return string
     */
	function getContent()
	{
		$output = $this->getText();
		if ($this->getAttribute('adm:htmlEditor')!==true)
		{

			$output = $this->encodePlainText();
			if ($this->getAttribute('forceP'))
			{
				$output = org_glizy_helpers_Html::forceP($output);
			}
			return $output;
		}
		else
		{
			$output = javascript_to_html( $output );
			// esegue la paginazione
			if (!is_null($this->getAttribute("paginate")))
			{
				$paginateClass 	= &$this->getAttribute("paginate");
				$this->_content = $paginateClass->splitTextResult($output);
			}

            if ($this->getAttribute('parseInternalLinks')) {
			    $output = org_glizy_helpers_Link::parseInternalLinks($output);
            }

			$attributes 				= array();
			$attributes['id'] 			= $this->getId();
			$attributes['class']		= $this->getAttribute('cssClass');
			$wrapTag = $this->getAttribute('wrapTag');
			$outputTag = !empty($wrapTag) ? '<'.$wrapTag.' '.$this->_renderAttributes($attributes).'>' : '';
			$outputTag .= $output;
			$outputTag .= !empty($wrapTag) ? '</'.$wrapTag.'>' : '';
			return $outputTag;
		}
	}

    /**
     * @return string
     */
	function encodePlainText()
	{
		return str_replace( array("\r\n", "\n", "\r"), "<br />", glz_encodeOutput( str_replace( '<br />', "\r\n", $this->getText() ) ) );
	}

    /**
     * @param $node
     *
     * @return string
     */
	public static function translateForMode_edit($node) {
		$attributes = array();
		$attributes['id'] = $node->getAttribute('id');
		$attributes['label'] = $node->getAttribute('label');
		$attributes['type'] = 'multiline';
		$attributes['data'] = '';

		if (count($node->attributes))
		{
			foreach ( $node->attributes as $index=>$attr )
			{
				if ($attr->prefix=="adm")
				{
					$attributes[$attr->name] = $attr->value;
				}
			}
		}
		if ($attributes['htmlEditor']=='true') {
			$attributes['data'] .= 'type=tinymce';
		}
		if (!isset($attributes['cssClass'])) {
			$attributes['cssClass'] = '{config:glizy.formElement.admCssClass}';
		}
		return org_glizy_helpers_Html::renderTag('glz:Input', $attributes);
	}
}