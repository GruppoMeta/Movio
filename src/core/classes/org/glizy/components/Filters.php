<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_components_Filters extends org_glizy_components_ComponentContainer
{
	var $_filters;

    /**
     * @param $result
     */
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

	/**
	 * Process
	 *
	 * @return	boolean	false if the process is aborted
	 * @access	public
	 */
	function process()
	{
		$this->_filters = array();
		// legge i valori dai figli
		for ($i=0; $i<count($this->childComponents);$i++)
		{
			if (method_exists($this->childComponents[$i], 'getItem') &&
				$this->childComponents[$i]->getAttribute( 'visible' ) &&
				$this->childComponents[$i]->getAttribute( 'enabled' ) )
			{
				$item = $this->childComponents[$i]->getItem();
				$this->_filters = array_merge($this->_filters, $item);
			}
		}

		$this->processChilds();
	}

	function render()
	{
	}

	function getFilters()
	{
		return $this->_filters;
	}

}