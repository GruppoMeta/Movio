<?php
class movio_modules_publishApp_views_components_SiteTreeView  extends org_glizycms_contents_views_components_SiteTreeView
{
}

class movio_modules_publishApp_views_components_SiteTreeView_render extends org_glizycms_contents_views_components_SiteTreeView_render
{
	function getDefaultSkin()
	{
		$skin = <<<EOD
<div id="treeview">
	<div id="treeview-title">
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