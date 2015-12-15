<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_components_EmptyComponent extends org_glizy_components_ComponentContainer
{
	function __construct(&$application, &$parent, $tagName='', $id='', $originalId='')
	{
		parent::__construct($application, $parent, $tagName, $id, $originalId);
		$this->canHaveChilds	= true;
		$this->overrideEditableRegion 	= false;
	}

	function getContent()
	{
		$result = array();
		for ($i=0; $i<count($this->childComponents);$i++)
		{
			$id = preg_replace('/([^\-]+)\-(.*)/', '$2', $this->childComponents[$i]->getId());
			$result[$id] = $this->childComponents[$i]->getContent();
		}

		return $result;
	}
}