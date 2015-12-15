<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_components_SelectPage extends org_glizy_components_Component
{
	/**
	 * Init
	 *
	 * @return	void
	 * @access	public
	 */
	function init()
	{
		// define the custom attributes
		$this->defineAttribute('label',				false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('required',			false, 	false,	COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('requiredMessage',	false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('startFrom',		false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('maxDepth',		false, 	false,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('pageType',		false, 	'',	COMPONENT_TYPE_STRING);
		// attributo aggiunto per compatibilità
		// il render HTML è abilitato solo se impostato a true
		$this->defineAttribute('renderHtml',		false, 	false,	COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('wrapTag',			false, 	'',	COMPONENT_TYPE_STRING);
		$this->defineAttribute('title',				false, 	'',	COMPONENT_TYPE_STRING);
		$this->defineAttribute('generateLink',		false, 	false,	COMPONENT_TYPE_BOOLEAN);

		// call the superclass for validate the attributes
		parent::init();
	}


	function process()
	{
		$this->_content = $this->_parent->loadContent($this->getId());
	}

	function getContent()
	{
		if ( !$this->getAttribute( 'generateLink' ) )
		{
			return (!is_null($this->_content) && $this->_content > 0) ? org_glizy_helpers_Link::makeURL('link', array('pageId' => $this->_content)) : '';
		}

		$title = $this->getAttribute( 'title' );
		if ( empty( $title ) && !empty( $this->_content ) ) {
			$siteMap = $this->_application->getSiteMap();
			$page = $siteMap->getNodeById(  $this->_content );
			$title = $page->title;
		}
		return (!is_null($this->_content) && $this->_content > 0) ? org_glizy_helpers_Link::makeLink('link', array('pageId' => $this->_content, 'title' => $title )) : '';
	}

	function render_html()
	{
		if ( $this->getAttribute( 'renderHtml' ) )
		{
			$tag = $this->getAttribute( 'wrapTag' );
			$label = ( !empty( $tag ) ? '<'.$tag.'>' : '' ) .$this->getAttribute( 'title' ).( !empty( $tag ) ? '</'.$tag.'>' : '' );
			$output = org_glizy_helpers_Link::makeLink('link', array( 'cssClass' => $this->getAttribute( 'cssClass' ), 'label' => $label, 'pageId' => $this->_content));
			$this->addOutputCode( $output );
		}
	}

	public static function translateForMode_edit($node) {
		$attributes = array();
		$attributes['id'] = $node->getAttribute('id');
		$attributes['label'] = $node->getAttribute('label');
		$attributes['required'] = $node->getAttribute('required');
		$attributes['xmlns:cms'] = "org.glizycms.views.components.*";

		return org_glizy_helpers_Html::renderTag('cms:SelectPage', $attributes);
	}
}
