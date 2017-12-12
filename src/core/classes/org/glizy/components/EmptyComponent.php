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
	private $currentRenderChildId;

	function __construct(&$application, &$parent, $tagName='', $id='', $originalId='')
	{
		parent::__construct($application, $parent, $tagName, $id, $originalId);
		$this->canHaveChilds	= true;
		$this->overrideEditableRegion 	= false;
	}

    public function init()
    {
    	// acceptOutput viene importato a true quando si vuole un skin in cui i valori
    	// dei figli siano già renderizzati
        $this->defineAttribute('acceptOutput', false, false, COMPONENT_TYPE_BOOLEAN);
        $this->defineAttribute('content', false, false, COMPONENT_TYPE_OBJECT);
        parent::init();
    }

    public function process()
    {
    	$this->acceptOutput = $this->getAttribute('acceptOutput') && $this->getAttribute('skin');

    	$value = $this->getAttribute('content');
    	if ($value) {
    		$this->setContent($value);
    	}

    	parent::process();
    }

	public function getContent()
	{
		if ($this->acceptOutput) {
			$result = $this->_content;
			$this->acceptOutput = false;
			// disabilita acceptOutput altrimenti il render della skin non viene visualizzato
		} else {
			$result = is_object($this->_content) || is_array($this->_content) ? $this->_content : array();
			for ($i=0; $i<count($this->childComponents);$i++)
			{
				$id = preg_replace('/([^\-]+)\-(.*)/', '$2', $this->childComponents[$i]->getId());
				$result[$id] = $this->childComponents[$i]->getContent();
			}
		}

		return $result;
	}

	public function render($outputMode=NULL, $skipChilds=false)
	{
		if ($this->acceptOutput) {
			if ($this->_content && is_array($this->_content)) {
				$this->_content = (object)$this->_content;
			} else if (!is_object($this->_content)) {
				$this->_content = new StdClass;
			}

			$numChild = count($this->childComponents);
			for ($i=0; $i<$numChild;$i++) {
				if ($this->childComponents[$i]->getAttribute('visible') && $this->childComponents[$i]->getAttribute('enabled')) {
					$this->currentRenderChildId = $this->childComponents[$i]->getId();
					$this->_content->{$this->currentRenderChildId} = '';
					$this->childComponents[$i]->render($outputMode);
					$this->state = COMPONENT_STATE_RENDER;
					if ($this->checkBreakCycle()) {
						$this->state = COMPONENT_STATE_BLOCKED;
						$this->breakCycle(false);
						break;
					}
				}
			}
		}

		parent::render($outputMode, $skipChilds);
	}

	public function addOutputCode($output, $editableRegion='', $atEnd=false)
	{
		if ($this->acceptOutput) {
			$this->_content->{$this->currentRenderChildId} = $output;
		} else {
			$this->addParentOutputCode($output, $editableRegion, $atEnd);
		}
	}
}