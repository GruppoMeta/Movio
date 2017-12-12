<?php
class movio_modules_ontologybuilder_controllers_relation_ajax_DelRelation extends org_glizy_mvc_core_CommandAjax
{
    function execute($id)
    {
        $this->checkPermissionForBackend();
        
        $relation = org_glizy_objectFactory::createModel('movio.modules.ontologybuilder.models.RelationTypesDocument');
        $relation->delete($id);

        $entityTypeService = $this->application->retrieveProxy('movio.modules.ontologybuilder.service.EntityTypeService');
        $entityTypeService->invalidate();

        $localeService = $this->application->retrieveProxy('movio.modules.ontologybuilder.service.LocaleService');
        $localeService->invalidate();

        return true;
    }
}
