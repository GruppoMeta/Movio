<?php
class movio_modules_ontologybuilder_views_renderer_EntityName extends GlizyObject
{
    function renderCell( $key, $value, $row )
    {
        $entityTypeId = str_replace('entity', '', $value);
        $application = org_glizy_ObjectValues::get('org.glizy', 'application' );
        $entityTypeService = $application->retrieveProxy('movio.modules.ontologybuilder.service.EntityTypeService');
        return $entityTypeService->getEntityTypeName($entityTypeId);
    }
}