<?php
class movio_modules_ontologybuilder_views_renderer_EntityName extends GlizyObject
{
    function renderCell( $key, $value, $row )
    {
        $application = org_glizy_ObjectValues::get('org.glizy', 'application' );
        $entityTypeService = $application->retrieveProxy('movio.modules.ontologybuilder.service.EntityTypeService');
        $localeService = $application->retrieveProxy('movio.modules.ontologybuilder.service.LocaleService');
        $language = $application->getEditingLanguage();

        $entityTypeId = str_replace('entity', '', $value);
        $entityName = $entityTypeService->getEntityTypeName($entityTypeId);
        return $localeService->getTranslation($language, $entityName);;
    }
}