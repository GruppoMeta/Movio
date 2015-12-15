<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_components_ComponentContainer extends org_glizy_components_Component
{
	var $_output;
	var $acceptOutput;
	var $overrideEditableRegion;

	function __construct(&$application, &$parent, $tagName='', $id='', $originalId='')
	{
		parent::__construct($application, $parent, $tagName, $id, $originalId);
		$this->canHaveChilds	= true;
		$this->_output 			= array();
		$this->acceptOutput 	= false;
		$this->overrideEditableRegion 	= true;
	}

	function addOutputCode($output, $editableRegion='', $atEnd=false)
	{
		if ($this->acceptOutput)
		{
			if ($this->overrideEditableRegion)
			{
				$editableRegion = $this->getAttribute('editableRegion');
			}
			$this->_output[] = array('editableRegion' => empty($editableRegion) ? $this->getEditableRegion() : $editableRegion, 'code' => $output, 'atEnd' => $atEnd);
		}
		else
		{
			$this->addParentOutputCode($output, $editableRegion, $atEnd);
		}
	}

	function addParentOutputCode($output, $editableRegion='', $atEnd=false)
	{
		parent::addOutputCode($output, $editableRegion, $atEnd);
	}

	function loadContent($id, $bindTo='')
	{
		return method_exists($this->_parent, 'loadContent') ? $this->_parent->loadContent($id,  $bindTo) : '';
	}

	function getChildContent()
	{
		$result = array();
		for ($i=0; $i<count($this->childComponents);$i++)
		{
			$onlyAdmin = $this->childComponents[$i]->getAttribute( "onlyAdmin" );
			if ( $onlyAdmin === true )
			{
				$result = array_merge( $result, $this->childComponents[$i]->getContent() );
			}
			else
			{
				$result[$this->childComponents[$i]->getOriginalId()] = $this->childComponents[$i]->getContent();
			}
		}
		return $result;
	}

	protected function stripIdFromContent($content)
    {
        $id = $this->getId();
        $result = array();
        foreach ($content as $k => $v) {
            $result[preg_replace('/^'.$id.'-/', '', $k)] = $v;
        }
        return $result;
    }
}