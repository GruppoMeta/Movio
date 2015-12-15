<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_application_SiteMapSimple extends org_glizy_application_SiteMap
{
	function loadTree($forceReload=false)
	{
		if ($forceReload) $this->init();

		$menu = $this->getEmptyMenu();
		$menu['id'] = '__root__';
		$this->_siteMapArray[$menu['id']] = $menu;

		$files = array();
		$dir = org_glizy_Paths::getRealPath('APPLICATION_PAGE_TYPE');
		if ($dirHandle = @opendir($dir))
		{
			while ($fileName = readdir($dirHandle))
			{
				if ($fileName!='.' &&
					$fileName!='..' &&
					!is_dir($dir.'/'.$fileName) &&
					strstr($fileName, '.xml')!==false)
				{
					$files[] = $fileName;
				}
			}
			closedir($dirHandle);
		}

		//ordina il risultato alfabeticamente
		sort($files);

		// legge la localizzazione dei nomi dei pageType
		glz_loadLocale( $dir );

		// crea i menù
		foreach($files as $f)
		{
			$fileName 			= substr($f, 0, strrpos($f, '.'));
			$title 				= __T($fileName);
			if (empty($title)) $title = $fileName;

			$menu 				= $this->getEmptyMenu();
			$menu['id'] 		= strtolower($fileName);
			$menu['parentId'] 	= $this->_searchParent($menu['id']);
			$menu['pageType'] 	= $fileName;
			$menu['title'] 		= $title;
			$menu['type'] 		= substr($fileName, 0, 1)=='_' ? 'SYSTEM' : 'PAGE';
			$this->_siteMapArray[$menu['id']] = $menu;
		}

		$this->_makeChilds();
	}

	function _searchParent($menuId)
	{
		$idParts = explode('_', $menuId);
		$parentMenuId = '';
		foreach($idParts as $p)
		{
			$parentMenuId .= $p;
			if (isset($this->_siteMapArray[$parentMenuId]))
			{
				return $parentMenuId;
			}
			$parentMenuId .= '_';
		}
		return '__root__';
	}
}