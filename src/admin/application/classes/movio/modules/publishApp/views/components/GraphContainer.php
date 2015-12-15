<?php
class movio_modules_publishApp_views_components_GraphContainer extends org_glizy_components_Panel
{
    function render()
    {
        parent::render();
        $this->addOutputCode( org_glizy_helpers_JS::linkJSfile(__Paths::get('STATIC_DIR').'dagre-d3/d3.v3.min.js'));
        $this->addOutputCode( org_glizy_helpers_JS::linkJSfile(__Paths::get('STATIC_DIR').'dagre-d3/dagre-d3.min.js'));
        $this->addOutputCode( org_glizy_helpers_JS::linkJSfile(__Paths::get('STATIC_DIR').'dagre-d3/graphlib-dot.min.js'));
    }
}