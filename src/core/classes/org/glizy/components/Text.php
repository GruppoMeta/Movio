<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_components_Text extends org_glizy_components_HtmlComponent
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
		$this->defineAttribute('cssClass',			false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('label',				false, 	'',	COMPONENT_TYPE_STRING);
		$this->defineAttribute('text',				false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('textIfEmpty',		false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('html',			false, 	false,	COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('wrapTag',			false, 	'',	COMPONENT_TYPE_STRING);
		$this->defineAttribute('adm:disabled',		false, 	false,	COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('adm:readOnly',		false, 	false,	COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('adm:maxLength',		false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('adm:size',			false, 	50,	COMPONENT_TYPE_INTEGER);
		$this->defineAttribute('adm:required',		false, 	false,	COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('model:required',	false, 	false,	COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('adm:requiredMessage',	false, 	NULL,	COMPONENT_TYPE_STRING);


		// call the superclass for validate the attributes
		parent::init();
	}


	function process()
	{
		$tagContent = $this->getText();
		if (empty($tagContent))
		{
			// richiede il contenuto al padre
			$tagContent = $this->_parent->loadContent($this->getId());
			$this->setText($tagContent);
		}

		$textIfEmpty = $this->getAttribute( 'textIfEmpty' );
		if (empty($tagContent) && !empty( $textIfEmpty ) )
		{
			$this->setText($textIfEmpty);
		}

		$this->processChilds();
	}


	function render_html()
	{
		$content = $this->getAttribute( 'html' ) ? $this->getText() : glz_encodeOutput($this->getText());
		if (!empty($content))
		{
			$attributes = array();
			$attributes 				= array();
			$attributes['id'] 			= $this->getId();
			$attributes['class']		= $this->getAttribute('cssClass');

			$wrapTag = $this->getAttribute('wrapTag');
			$output  = '';
			$output .= !empty($wrapTag) ? '<'.$wrapTag.' '.$this->_renderAttributes($attributes).'>' : '';
				$output .= $content;
			$output .= !empty($wrapTag) ? '</'.$wrapTag.'>' : '';
			$this->addOutputCode($output);
		}
	}


	function getContent()
	{
		$content = $this->getAttribute( 'html' ) ? $this->getText() : glz_encodeOutput($this->getText());
		return $content;
	}

	public static function translateForMode_edit($node) {
		$attributes = array();
		$attributes['id'] = $node->getAttribute('id');
		$attributes['label'] = $node->getAttribute('label');
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
		if (!isset($attributes['cssClass'])) {
			$attributes['cssClass'] = '{config:glizy.formElement.admCssClass}';
		}
		return org_glizy_helpers_Html::renderTag('glz:Input', $attributes);
	}
}