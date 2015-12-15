<?php
class movio_modules_ontologybuilder_controllers_entityLabel_ajax_DelEntityLabel extends org_glizy_mvc_core_CommandAjax
{
    function execute($id)
    {
        $label = org_glizy_objectFactory::createModel('movio.modules.ontologybuilder.models.EntityLabelsDocument');
        $label->delete($id);

        $localeService = $this->application->retrieveProxy('movio.modules.ontologybuilder.service.LocaleService');
        $localeService->invalidate();
    }
}
?>