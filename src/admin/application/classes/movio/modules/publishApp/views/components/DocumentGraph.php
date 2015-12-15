<?php
class movio_modules_publishApp_views_components_DocumentGraph extends movio_modules_ontologybuilder_views_components_DocumentGraph
{
    protected function makeUrl($entityTypeId, $document_id)
    {
        return 'movioContent:'.$document_id;
    }
}