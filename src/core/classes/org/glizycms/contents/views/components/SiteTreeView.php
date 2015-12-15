<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2011 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizycms_contents_views_components_SiteTreeView  extends org_glizy_components_Component
{
	/**
	 * Init
	 *
	 * @return	void
	 * @access	public
	 */
	public function init()
	{
		$this->defineAttribute('addLabel', false, '{i18n:glizycms.Add Page}', COMPONENT_TYPE_STRING);
		$this->defineAttribute('title', false, '{i18n:glizycms.Site Structure}',	COMPONENT_TYPE_STRING);

		// call the superclass for validate the attributes
		parent::init();
	}


	public function process() {
		$this->_content =  new org_glizycms_contents_views_components_SiteTreeViewVO();
		$this->_content->addLabel = $this->getAttribute('addLabel');
		$this->_content->title = $this->getAttribute('title');
		$this->_content->ajaxUrl = $this->getAjaxUrl();
		$this->_content->addUrl = __Routing::makeUrl('linkChangeAction', array('action' => 'add'));
	}

	public function render($outputMode=NULL, $skipChilds=false) {
		parent::render($outputMode, $skipChilds);
		if (!org_glizy_ObjectValues::get('org.glizycms.js', 'jsTree', false))
		{
			org_glizy_ObjectValues::set('org.glizycms.js', 'jsTree', true);
			$this->addOutputCode( org_glizy_helpers_JS::linkStaticJSfile( 'jquery/jquery-jstree/jquery.jstree.js' ) );
			$this->addOutputCode( org_glizy_helpers_JS::linkStaticJSfile( 'jquery/jquery-jstree/jquery.cookie.js' ) );
		}
	}
}

class org_glizycms_contents_views_components_SiteTreeViewVO
{
	public $addLabel;
	public $title;
	public $ajaxUrl;
	public $addUrl;
}

class org_glizycms_contents_views_components_SiteTreeView_render extends org_glizy_components_render_Render
{
	function getDefaultSkin()
	{
		$skin = <<<EOD
<div id="treeview">
	<div id="treeview-title">
		<a id="js-glizycmsSiteTreeAdd" tal:attributes="href Component/addUrl"><i class="icon-plus"></i> <span tal:omit-tag="" tal:content="Component/addLabel" /></a>
		<h3 tal:content="Component/title"></h3>
	</div>
	<div id="treeview-inner">
		<div id="js-glizycmsSiteTree" tal:attributes="data-ajaxurl Component/ajaxUrl"></div>
	</div>
	<div id="openclose">
		<i class="icon-chevron-left"></i>
	</div>
</div>
EOD;
		return $skin;
	}
}