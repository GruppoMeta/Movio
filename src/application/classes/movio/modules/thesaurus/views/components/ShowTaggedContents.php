<?php
class movio_modules_thesaurus_views_components_ShowTaggedContents extends org_glizy_components_ComponentContainer
{
    function process()
    {
        $this->_content = array();
        $termId = __Request::get('termId');
        if ($termId) {
            $thesaurusProxy = org_glizy_ObjectFactory::createObject('movio.modules.thesaurus.models.proxy.ThesaurusProxy');
            $term = $thesaurusProxy->loadTerm($termId);
            $this->_content = $thesaurusProxy->getDocumentsWithTerm($termId);
        }
    }
}

class movio_modules_thesaurus_views_components_ShowTaggedContents_render extends org_glizy_components_render_Render
{
    function getDefaultSkin()
    {
        $skin = <<<EOD
<div class="results-content" tal:condition="php: count(Component)">
    <div class="item clearfix" tal:repeat="item Component">
        <h1><a tal:attributes="href item/url; title item/title" tal:content="structure item/title"></a></h1>
        <p tal:condition="item/description" tal:content="structure item/description" />
    </div>
</div>
<span tal:omit-tag="" tal:condition="php: empty(Component)">
    <p tal:content="php:__Tp('MW_NO_RECORD_FOUND')"></p>
</span>
EOD;
        return $skin;
    }
}