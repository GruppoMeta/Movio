<?php
class movio_modules_ontologybuilder_controllers_relation_ajax_SetRelation extends org_glizy_mvc_core_CommandAjax
{
    function execute()
    {
        $this->checkPermissionForBackend();
        
        $relation = org_glizy_objectFactory::createModel('movio.modules.ontologybuilder.models.RelationTypesDocument');
        $relation->load(__Request::get('pk'));
        $field = __Request::get('name');

        if (preg_match("/translation.(.+)/", $field, $m)) {
            $language = $m[1];
            $translation = $relation->translation;
            $translation[$language] = __Request::get('value');
            $relation->translation = $translation;
        }
        else {
            $relation->$field = __Request::get('value');
        }

        $relation->save();

        $entityTypeService = $this->application->retrieveProxy('movio.modules.ontologybuilder.service.EntityTypeService');
        $entityTypeService->invalidate();

        $localeService = $this->application->retrieveProxy('movio.modules.ontologybuilder.service.LocaleService');
        $localeService->invalidate();
    }
}
?>