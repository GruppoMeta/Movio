<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_application_SiteMapIterator extends GlizyObject
{
	var $_treeManager;
	var $EOF;
	var $_currentNode;

	function __construct(&$parent)
	{
		$this->_treeManager 	= &$parent;
		$this->EOF = false;
		$this->_currentNode = $this->_treeManager->getHomeNode();

	}

	function moveNext()
	{
		if ($this->_currentNode->hasChildNodes())
		{
			$tempNode = $this->_currentNode->firstChild();
		}
		else
		{
			$tempNode = $this->_currentNode->nextSibling();
			if (is_null($tempNode))
			{
				$tempNode = $this->_currentNode;
				while (true)
				{
					$node = $tempNode->parentNode();

					if (!is_null($node))
					{
						$node2 = $node->nextSibling();
						if (!is_null($node2))
						{
							$tempNode = $node2;
							break;
						}
						else
						{
							$tempNode = $node;
						}
					}
					else
					{
						$tempNode = null;
						break;
					}
				}
			}
		}

		$this->_update($tempNode);
		return $this->getNode();
	}

	function movePrevious()
	{
		$tempNode = $this->_currentNode->previousSibling();
		if (is_null($tempNode)) {
			$tempNode = $this->_currentNode->parentNode();
		} else {
			while (true)
			{
				$tempNodeChild = $tempNode->childNodes();
				if (!count($tempNodeChild)) {
					break;
				}
				$tempNode = array_pop($tempNodeChild);
			}
		}

		$this->_update($tempNode);
		return $this->getNode();
	}

	function &getNode()
	{
		return $this->_currentNode;
	}

	function &getNodeArray()
	{
		return $this->_treeManager->_siteMapArray[$this->_currentNode->id];
	}

	function setNode($node)
	{
		$this->_currentNode = $node;
	}

	function reset()
	{
		$this->EOF = false;
		$this->_currentNode = $this->_treeManager->getHomeNode();
	}

	function _update(&$node)
	{
		$this->EOF = is_null($node);
		$this->_currentNode = $node;
	}


	function hasMore()
	{
		return !$this->EOF;
	}
}