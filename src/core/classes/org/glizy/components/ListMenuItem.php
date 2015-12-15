<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_components_ListMenuItem extends org_glizy_components_Component
{
	/**
	 * Init
	 *
	 * @return	void
	 * @access	public
	 */
	function init()
	{
		$this->defineAttribute('url',			false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('routeUrl',		false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('label',			false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('value',			false, 	'',	COMPONENT_TYPE_STRING);
		$this->defineAttribute('selected',		false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('acl',			false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('cssClass',		false, 	'',	COMPONENT_TYPE_STRING);
		$this->defineAttribute('wrapTag',		false, 	'',	COMPONENT_TYPE_STRING);

		// call the superclass for validate the attributes
		parent::init();
	}


	function getItem()
	{
		$acl = $this->getAttribute( 'acl' );

		if ( !empty( $acl ) )
		{
			list( $service, $action ) = explode( ',', $acl );
			if ( !$this->_user->acl( $service, $action ) )
			{
			return false;
			}
		}
		$value = strtolower( $this->getAttribute( 'value' ) );
		$label = $this->getAttribute( 'label' );
		$url = $this->getAttribute( 'url' );
		$routeUrl = $this->getAttribute( 'routeUrl' );
		$cssClass = $this->getAttribute( 'cssClass' );

		if ( $routeUrl )
		{
			$title = $label;
			if ( $cssClass )
			{
				$label = '<i class="'.$cssClass.'"></i>'.$label;
			}
			$label = $this->addWrap($label);
			$url = org_glizy_helpers_Link::makeLink( $routeUrl, array( 'pageId' => $value, 'title' => $title, 'label' => $label ), array(), '', false );
			$condition = $this->getAttribute( 'selected' );
			if ( !$condition ) $condition = $value;
			$condition = explode( ',', $condition );

			if ( count( $condition ) > 1 )
			{
				$selected = false;
				for ($i=0; $i < count( $condition ); $i++)
				{
					if ( __Request::get( $condition[ $i ] ) == $condition[ $i + 1 ] )
					{
						$selected = true;
						break;
					}
					$i++;
				}
			}
			else
			{
				$selected = $value == $this->_application->getPageId();
			}

			return array( 'url' => $url, 'selected' => $selected );
		}
		else if ( $url )
		{
			return array( 'url' => org_glizy_helpers_Link::makeSimpleLink($this->addWrap($label), $url, $label, $cssClass ), 'selected' => $value == __Request::get( '__url__' ) );
		}
		else if ( $value )
		{
            $url = org_glizy_helpers_Link::makeLink( $value, array( 'label' => $this->addWrap($label),'title' => $label, 'cssClass' => $cssClass ), array(), '', false );
			return array( 'url' => $url, 'selected' => $value == __Request::get( '__url__' ) );
		}
		else
		{
			return array( 'url' => '<span class="'.$cssClass.'">'.$label.'</span>', 'selected' => false );
		}
	}

	private function addWrap($label) {
		$wrapTag = $this->getAttribute('wrapTag');
		if ($wrapTag) {
			$label = '<'.$wrapTag.'>'.$label.'</'.$wrapTag.'>';
		}
		return $label;
	}
}