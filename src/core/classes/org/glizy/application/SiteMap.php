<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_application_SiteMap extends GlizyObject
{
	var $_siteMapArray;
	var $_pageTypeMap;
	var $_type = '';
	var $_user = '';

	private $isAdmin;
	private $hidePrivatePage;

	function __construct()
	{
		$this->init();
	}

	function init()
	{
		$this->_siteMapArray 		= array();
		$this->_pageTypeMap			= array();
		$application = & org_glizy_ObjectValues::get('org.glizy', 'application');
		$this->_user = &$application->getCurrentUser();
		$this->isAdmin = $application->isAdmin();
        $this->hidePrivatePage = org_glizy_Config::get( 'HIDE_PRIVATE_PAGE', true );
	}

	function getType()
	{
		return $this->_type;
	}

	function loadTree($forceReload=false)
	{
	}

	function numPages()
	{
		return count($this->_siteMapArray);
	}


	function _makeChilds()
	{
		// ripassa l'array creando i link parente creando l'aray per mappare pageId con ID
		$menuIdToSort = array();
		$IDs = array_keys($this->_siteMapArray);
		foreach ($IDs as $key)
		{
			$menuNode = &$this->_siteMapArray[$key];
			$parentId = $menuNode['parentId'];

			if ( !is_bool( $menuNode['isVisible'] ) )
			{
				if (preg_match("/\{php\:.*\}/i", $menuNode['isVisible'] ) )
				{
					$phpcode = org_glizy_helpers_PhpScript::parse( $menuNode['isVisible'] );
					$menuNode['isVisible'] = eval($phpcode)==1 ? true : false;
					$menuNode['hideByAcl'] = !$menuNode['isVisible'];
				}
				else
				{
					$menuNode['isVisible'] = $menuNode['isVisible']==='false' ? false : true;
				}
			}

			if ( !$this->isAdmin && $menuNode['isLocked'] && !$this->_user->isLogged() && $this->hidePrivatePage ) {
				$menuNode['isVisible'] = false;
			}

			$this->_pageTypeMap[strtolower($menuNode['pageType'])] = &$menuNode;
			if (($this->_type=='db' && $parentId==0) || ($this->_type=='xml' && $parentId=='') || ($this->_type=='' && $parentId===0)) continue;

			if (isset($this->_siteMapArray[$parentId]))
			{
				$menuNodeParent = &$this->_siteMapArray[$parentId];

				// aggiunge il riferimento come childNode
				$menuNodeParent['childNodes'][] = $menuNode['id'];
				$menuNode['depth'] 				= $menuNodeParent['depth']+1;
			}
			else
			{
				// cancella il menu
				// perché è orfano
				unset($this->_siteMapArray[$key]);
				// TODO cancellare anche i figli in modo ricorsivo
			}

			if ($menuNode['sortChild']) {
				$menuIdToSort[] = $key;
			}
		}

		// order the menus
		foreach($menuIdToSort as $key) {
			$menuNode = &$this->_siteMapArray[$key];
			$tempNode = array();
			foreach($menuNode['childNodes'] as $childId) {
				$tempNode[$childId] = &$this->_siteMapArray[$childId];
			}
			usort($tempNode, function($a, $b) {
				if (!$a['title'] && !$b['title']) {
					return 0;
				} else if (!$a['title']) {
					return 1;
				} else if (!$b['title']) {
					return -1;
				} else {
					return strnatcasecmp($a['title'], $b['title']);
				}
			});
			$menuNode['childNodes'] = array();
			foreach($tempNode as $node) {
				$menuNode['childNodes'][] = $node['id'];
			}
		}

		$this->_makeDepth($IDs[0], 0);
	}

	function _makeDepth($id, $parentDepth)
	{
		if ($id) {
			$menu = &$this->_siteMapArray[$id];
			$menu['depth'] = $parentDepth+1;
			foreach($menu['childNodes'] as $m)
			{
				$this->_makeDepth($m, $parentDepth+1);
			}
		}
	}

	/* */
	function &getNodeById($id)
	{
		$id = is_numeric($id) ? $id : strtolower($id);
		if (!array_key_exists($id, $this->_siteMapArray)) {
			$a = NULL;
			return $a;
		}

		if (!is_object($this->_siteMapArray[$id]['nodeObj']))
		{
			$a =  new org_glizy_application_SiteMapNode($this, $this->_siteMapArray[$id]);
			$this->_siteMapArray[$id]['nodeObj'] = &$a;
			return $a;
		}
		return $this->_siteMapArray[$id]['nodeObj'];
	}

	function &getMenuByPageType($pageType)
	{
		$pageType = strtolower($pageType);
		if (!array_key_exists($pageType, $this->_pageTypeMap)) return NULL;

		if (!is_object($this->_pageTypeMap[$pageType]['nodeObj']))
		{
			$a = new org_glizy_application_SiteMapNode($this, $this->_pageTypeMap[$pageType]);
			$this->_pageTypeMap[$pageType]['nodeObj'] = &$a;
			return $a;
		}
		return $this->_pageTypeMap[$pageType]['nodeObj'];
	}

	function &getHomeNode()
	{
		$IDs = array_keys($this->_siteMapArray);
		return $this->getNodeById($IDs[0]);
	}

	function &getSiteArray($forceReload=false)
	{
		if (!$this->_siteMapArray || $forceReload) {
		$this->loadTree($forceReload);
		}
		return $this->_siteMapArray;
	}

	function getAllChildsId($id)
	{
		$result = array();
		$this->_getAllChildsId($id, $result);
		return $result;
	}

	function _getAllChildsId($id, &$result)
	{
		if (!array_key_exists($id, $this->_siteMapArray)) return $result;
		$result[] = $id;
		$menu = $this->_siteMapArray[$id];
		$childNodes = $menu['childNodes'];
		foreach ($childNodes as $childId)
		{
			$this->_getAllChildsId($childId, $result);
		}
	}

	function addChildMenu(&$menu, $menuRecord)
	{
		$menuRecord['childNodes'] 	= array();
		$menuRecord['parentId']		= $menu->id;
		$menuRecord['depth'] 		= ($menu->depth)+1;
		$menuRecord['nodeObj'] 		= NULL;
		$menuRecord['isPublished'] 	= 1;
		$menuRecord['order'] 		= 1;
		$menuRecord['hasPreview'] 	= 1;
		$menuRecord['type'] 		= 'PAGE';
		$menuRecord['isLocked'] 	= false;
		$menuRecord['nodeObj'] 		= NULL;
		if ( !isset( $menuRecord['printPdf'] ) )
		{
			$menuRecord['printPdf'] = true;
		}

		$this->_siteMapArray[$menuRecord['id']] = $menuRecord;
		$this->_pageTypeMap[strtolower($menuRecord['pageType'])] = &$this->_siteMapArray[$menuRecord["id"]];
		$this->_siteMapArray[$menu->id]['childNodes'][] = $menuRecord['id'];
		unset($this->_siteMapArray[$menu->id]['nodeObj']);
	}

	function getEmptyMenu()
	{
		$menu = array();
		$menu['id'] 				= 0;
		$menu['parentId'] 			= 0;
		$menu['pageType'] 			= '';
		$menu['isVisible'] 			= 1;
		$menu['hideByAcl'] 	= false;
		$menu['title'] 				= '';
		$menu['titleLink'] 			= '';
		$menu['linkDescription'] 	= '';
		$menu['showTitle'] 			= true;
		$menu['depth'] 				= 1;
		$menu['childNodes'] 		= array();
		$menu['order'] 				= 1;
		$menu['type'] 				= 'PAGE';
		$menu['creationDate'] 		= time();
		$menu['modificationDate'] 	= time();
		$menu['isLocked'] 			= false;
		$menu['nodeObj'] 			= NULL;
		$menu['hasComment'] 		= false;
		$menu['printPdf'] 			= true;
		$menu['cssClass'] 			= '';
		$menu['sortChild'] 			= false;
		return $menu;
	}
}