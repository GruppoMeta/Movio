<?php
class movio_modules_thesaurus_views_components_DictionaryTreeView  extends org_glizycms_contents_views_components_SiteTreeView
{
    private $dictionaryId;
    public function process() {
        $this->_content =  new org_glizycms_contents_views_components_SiteTreeViewVO();
        $this->dictionaryId = __Request::get('dictionaryId');
        $this->_content->addLabel = $this->getAttribute('addLabel');
        $this->_content->title = $this->getAttribute('title');
        $this->_content->ajaxUrl = $this->getAjaxUrl();
        $this->_content->addUrl = __Routing::makeUrl('linkChangeAction',
                                                      array('action' => 'add'),
                                                      array('dictionaryId' => $this->dictionaryId)
                                                    );
    }

    public function getAjaxUrl()
    {
        return 'ajax.php?pageId='.$this->_application->getPageId().'&ajaxTarget='.$this->getId().'&dictionaryId='.$this->dictionaryId.'&action=';
    }
}

class movio_modules_thesaurus_views_components_DictionaryTreeView_render extends org_glizycms_contents_views_components_SiteTreeView_render
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