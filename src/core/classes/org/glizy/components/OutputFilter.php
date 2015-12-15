<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_components_OutputFilter extends org_glizy_components_Component
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
		$this->defineAttribute('tag',					false, 	NULL, 	COMPONENT_TYPE_STRING);
		$this->defineAttribute('filter', 				true, 	NULL, 	COMPONENT_TYPE_STRING);
		$this->defineAttribute('mode', 					false,	'PRE', 	COMPONENT_TYPE_STRING);

		// call the superclass for validate the attributes
		parent::init();
		$this->doLater($this, '_setOutputFilter');
	}

	function _setOutputFilter()
	{
		$filterName = $this->getAttribute('filter');
		$tag = $this->getAttribute('tag');
		// risovle il nome della classe
		if (file_exists(org_glizy_Paths::get('CORE_CLASSES').'org/glizy/filters/'.$filterName.'.php'))
		{
			$filterName = 'org.glizy.filters.'.$filterName;
			glz_import($filterName);
		}
		else
		{
			glz_import($filterName);
		}

		$className = str_replace('.', '_', $filterName);
		if (class_exists($className))
		{
			// aggiunge il filtro per essere processato
			if ($this->getAttribute('mode')=='PRE')
			{
				$outputFilters = &org_glizy_ObjectValues::get('org.glizy:components.Component', 'OutputFilter.pre');
			}
			else
			{
				$outputFilters = &org_glizy_ObjectValues::get('org.glizy:components.Component', 'OutputFilter.post');
			}
			if (!isset($outputFilters[$tag])) $outputFilters[$tag] = array();
			$outputFilters[$tag][] = $className;
		}
	}
}