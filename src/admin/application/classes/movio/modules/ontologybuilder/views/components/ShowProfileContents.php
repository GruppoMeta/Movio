<?php
class movio_modules_ontologybuilder_views_components_ShowProfileContents extends org_glizy_components_ComponentContainer
{
    // TODO: spostare di posizione
    // TODO: verificare il pagetype
    function process()
    {
        $groupId = __Request::get('groupId');

        if ($groupId) {
            $entityProxy = org_glizy_ObjectFactory::createObject('movio.modules.ontologybuilder.models.proxy.EntityProxy');
            $this->_content = $entityProxy->getContentsByProfile($groupId);
        }
    }
}

class movio_modules_ontologybuilder_views_components_ShowProfileContents_render extends org_glizy_components_render_Render
{
    function getDefaultSkin()
    {
        $skin = <<<EOD
<section class="results-content" tal:condition="php: count(Component)">
    <article class="item clearfix" tal:repeat="item Component">
        <h1><a tal:attributes="href item/url; title item/title" tal:content="structure item/title"></a></h1>
        <p tal:condition="item/description" tal:content="structure item/description" />
    </article>
</section>

<span tal:omit-tag="" tal:condition="php: empty(Component)">
    <p tal:content="php:__Tp('MW_NO_RECORD_FOUND')"></p>
</span>
EOD;
        return $skin;
    }
}