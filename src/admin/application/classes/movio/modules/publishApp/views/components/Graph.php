<?php
class movio_modules_publishApp_views_components_Graph extends movio_modules_ontologybuilder_views_components_Graph
{
    protected $graphData = '';
    
    protected function makeUrl($pageId, $title)
    {
        return 'internal:'.$pageId;
    }
    
    function addOutputCode($output, $editableRegion='', $atEnd=null)
    {
        $this->graphData .= $output;
    }
    
    public function getGraphData()
    {
        return $this->graphData;
    }
}