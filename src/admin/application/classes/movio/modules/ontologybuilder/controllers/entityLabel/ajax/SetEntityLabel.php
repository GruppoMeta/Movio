<?php
class movio_modules_ontologybuilder_controllers_entityLabel_ajax_SetEntityLabel extends org_glizy_mvc_core_CommandAjax
{
    function execute()
    {
        $this->checkPermissionForBackend();
        
        $label = org_glizy_objectFactory::createModel('movio.modules.ontologybuilder.models.EntityLabelsDocument');
        $label->load(__Request::get('pk'));
        $field = __Request::get('name');

        if (preg_match("/translation.(.+)/", $field, $m)) {
            $language = $m[1];
            $translation = $label->translation;
            $translation[$language] = __Request::get('value');
            $label->translation = $translation;
        }
        else {
            $label->$field = __Request::get('value');
        }

        $label->save();

        $localeService = $this->application->retrieveProxy('movio.modules.ontologybuilder.service.LocaleService');
        $localeService->invalidate();
    }
}
?>