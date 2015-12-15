<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizy_application_SiteMapNode extends GlizyObject
{
	var $_treeManager;
	var $id;
	var $parentId;
	var $pageType;
	var $isPublished;
	var $isVisible;
	var $title;
	var $titleLink;
	var $linkDescription;
	var $order;
	var $hasPreview;
	var $depth;
	var $type;
	var $isLocked;
	var $creationDate;
	var $modificationDate;
	var $showTitle;
	var $moduleClass;
	var $url = NULL;
	var $select = NULL;
	var $hasComment;
	var $printPdf;
	var $cssClass;
	var $icon;
    var $extendsPermissions;
    var $keywords = '';
    var $description = '';
    var $extraData = null;

	function __construct(&$parent, &$node)
	{
		$this->_treeManager 		= &$parent;
		$this->attributes 			= &$node;
		$this->id 					= $this->attributes['id'];
		$this->parentId 			= $this->attributes['parentId'];
		$this->pageType 			= $this->attributes['pageType'];
		$this->title 				= javascript_to_html($this->attributes['title']);
		$this->titleLink 			= javascript_to_html($this->attributes['titleLink']);
		$this->order 				= $this->attributes['order'];
		$this->isVisible 			= $this->attributes['isVisible'];
		$this->depth 				= $this->attributes['depth'];
		$this->type 				= $this->attributes['type'];
		$this->isLocked 			= $this->attributes['isLocked'];
		$this->creationDate 		= $this->attributes['creationDate'];
		$this->modificationDate 	= $this->attributes['modificationDate'];
		$this->showTitle 			= $this->attributes['showTitle'];
		$this->hasComment 			= $this->attributes['hasComment'];
		$this->printPdf 			= $this->attributes['printPdf'] == 1;
		$this->cssClass 			= $this->attributes['cssClass'];
		$this->icon 				= $this->attributes['icon'];
		if (isset($this->attributes['linkDescription'])) $this->linkDescription = javascript_to_html($this->attributes['linkDescription']);
		if (isset($this->attributes['moduleClass'])) $this->url = $this->attributes['moduleClass'];
		if (isset($this->attributes['extendsPermissions'])) $this->url = $this->attributes['extendsPermissions'];
		if (isset($this->attributes['url'])) $this->url = $this->attributes['url'];
		if (isset($this->attributes['select'])) $this->select = $this->attributes['select'];
	}


	function hasChildNodes()
	{
		return count($this->attributes['childNodes'])>0;
	}

	function &firstChild($onlyVisible=false)
	{
		if (!$this->hasChildNodes()) return NULL;
		$menu = NULL;
		foreach($this->attributes['childNodes'] as $id) {
			$menu = $this->_treeManager->getNodeById($id);
			if (!$onlyVisible || $menu->isVisible) {
				break;
			}
			$menu = null;
		}
		return $menu;
	}

	function &childNodes()
	{
		$childNodes = array();
		if ($this->hasChildNodes())
		{
			foreach ($this->attributes['childNodes'] as $id)
			{
				$childNodes[] = $this->_treeManager->getNodeById($id);
			}
		}

		return $childNodes;
	}


	function &parentNode()
	{
		return $this->_treeManager->getNodeById($this->parentId);
	}

	function &parentNodeByDepth($depth)
	{
		$r = NULL;
		if ($this->depth<$depth)
		{
			return $r;
		}
		else if ($this->depth==$depth)
		{
			return $this;
		}
		else
		{
			$menu = &$this;
			while (true)
			{
				$tempNode = &$menu->parentNode();
				if ($tempNode->depth==$depth)
				{
					return $tempNode;
				}
				else if ($tempNode->depth==0)
				{
					return $r;
				}

				$menu = &$tempNode;
			}
		}
	}

	function &nextSibling()
	{
    	if ($this->parentId === 0) return NULL;
		$parentNode = $this->_treeManager->getNodeById($this->parentId);
		$childNodes = &$parentNode->attributes['childNodes'];
		if ($childNodes === NULL) return NULL;
        $pos = array_search($this->id, $childNodes);

        if ($pos<count($childNodes)-1)
		{
			return $this->_treeManager->getNodeById($childNodes[++$pos]);
		}
		else
		{
			return NULL;
		}
	}

	function &previousSibling()
	{
		if ($this->parentId === 0) return NULL;
		$parentNode = $this->_treeManager->getNodeById($this->parentId);
		$childNodes = &$parentNode->attributes['childNodes'];
	    if ($childNodes === NULL) return NULL;
        $pos = array_search($this->id, $childNodes);

        if ($pos>0)
		{
			return $this->_treeManager->getNodeById($childNodes[--$pos]);
		}
		else
		{
			return NULL;
		}
	}

	function getAttribute($attribute)
	{
		// TODO controllare che l'attrinbuto esiste
		return $this->attributes[$attribute];
	}

	function loadDetails()
	{
		if (is_integer($this->id)) {
			$application = &org_glizy_ObjectValues::get('org.glizy', 'application');
			$ar = &org_glizy_ObjectFactory::createModel('org.glizycms.core.models.MenuDetail');
			$ar->menudetail_FK_menu_id = $this->id;
			$ar->menudetail_FK_language_id = $application->getLanguageId();
			$ar->find();
			$values = $ar->getValuesAsArray();
			foreach ($values as $k=>$v)
			{
				$propName = str_replace('menudetail_', '', $k);
				if ($propName=='id') continue;
				$this->$propName = glz_encodeOutput($v);
			}
		}
	}

	function getSiteMap()
	{
		return $this->_treeManager;
	}
}