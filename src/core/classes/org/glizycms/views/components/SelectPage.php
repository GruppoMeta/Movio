<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizycms_views_components_SelectPage extends org_glizy_components_List
{
	function init()
	{
		// define the custom attributes
		$this->defineAttribute('startFrom',		false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('maxDepth',		false, 	false,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('pageType',		false, 	'',	COMPONENT_TYPE_STRING);
		$this->defineAttribute('value',		false, 	'',	COMPONENT_TYPE_STRING);

		// call the superclass for validate the attributes
		parent::init();
	}


	function process()
	{
		$this->_content = $this->_parent->loadContent($this->getId());
		if (!$this->_content) {
			$this->_content = $this->getAttribute('value');
		}

		$this->_items = array();
		if ($this->_application->isAdmin()) {
			if (!is_null($this->getAttribute('emptyValue')))
			{
				$this->_items[] = array( array('key' => '', 'value' => html_entity_decode( $this->getAttributeString('emptyValue') ), 'selected' => false, 'options' => '') );
			}
			$this->getItems();
		}
	}



	function getItems()
	{
		$menuProxy = org_glizy_ObjectFactory::createObject('org.glizycms.contents.models.proxy.MenuProxy');
		$siteMap = $menuProxy->getSiteMap();

		$siteMapIterator = &org_glizy_ObjectFactory::createObject('org.glizy.application.SiteMapIterator', $siteMap);
		$menus = array();
		// $menus[] = array('-', '', 0);
		$maxDepth = org_glizy_Config::get('ADM_SITE_MAX_DEPTH');
		$maxDepth = !is_null($maxDepth) && $this->getAttribute('maxDepth') ? $maxDepth : NULL;
		$pageTypeFilter = $this->getAttribute('pageType');
		$startFrom = $this->getAttribute('startFrom');

		if ( is_null( $startFrom ) )
		{
			while (!$siteMapIterator->EOF)
			{
				$n = $siteMapIterator->getNodeArray();
				$siteMapIterator->moveNext();

				if ((!is_null($maxDepth ) && $n['depth'] > $maxDepth) ||
					($n['depth']==1 && !$this->_user->acl(__Config::get('SITEMAP_ID'), 'new') && $this->_content  != 1 ) )
				{
					continue;
				}
				$pad = str_repeat('.  ', $n['depth']-1);
				if ( !empty( $pageTypeFilter ) )
				{
					if ( $pageTypeFilter != $n['pageType'] )
					{
						continue;
					}
					$pad = '';
				}
				$this->_items[] = array('key' => $n['id'], 'value' => $pad.(trim(strip_tags($n['title']))), 'selected' => $n['id']==$this->_content ? 1 : 0, 'options' => $n['pageType']);
			}
		}
		else
		{
			$found = false;
			$menuDepth = 0;
			while (!$siteMapIterator->EOF)
			{
				$n = $siteMapIterator->getNodeArray();
				$siteMapIterator->moveNext();
				if ( $n['id'] == $startFrom )
				{
					$found = true;
					$menuDepth = $n['depth'];
					continue;
				}

				if ( $found )
				{
					if ( $menuDepth == $n['depth']) break;

					if ( !empty( $pageTypeFilter ) )
					{
						if ( $pageTypeFilter != $n['pageType'] )
						{
							continue;
						}
					}
					$this->_items[] = array('key' => $n['id'], 'value' => trim(strip_tags($n['title'])), 'selected' => $n['id']==$this->_content ? 1 : 0, 'options' => $n['pageType']);
				}
			}
		}
	}
}