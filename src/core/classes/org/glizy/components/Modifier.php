<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_components_Modifier extends org_glizy_components_Component
{
	/**
	 * Init
	 *
	 * @return	void
	 * @access	public
	 */
	function init()
	{
		$this->defineAttribute('attribute',		true,	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('value', 		true,	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('target', 		true,	NULL,	COMPONENT_TYPE_OBJECT);
		$this->defineAttribute('reprocess',		false,	false,	COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('preprocess',	false,	false,	COMPONENT_TYPE_BOOLEAN);
		$this->doLater($this, 'preProcess');
		parent::init();
	}

	function preProcess()
	{
		if ($this->getAttribute('preprocess')) {
			$this->modifyComponent();
		}
	}

	function process()
	{
		if (!$this->getAttribute('preprocess')) {
			$this->modifyComponent();
		}
	}

	private function modifyComponent()
	{
		if ( !is_null($this->getAttribute('target')) && $this->getAttribute( 'enabled' ) )
		{
			$component = &$this->getAttribute('target');
			$value = $this->getAttribute('value');
			$component->setAttribute($this->getAttribute('attribute'), is_object($value) ? '{'.$value->getId().'}' : $value);
			if ($this->getAttribute('reprocess'))
			{
				$component->process();
			}
		}
	}

	public static function translateForMode_edit($node) {
		return '';
	}
}