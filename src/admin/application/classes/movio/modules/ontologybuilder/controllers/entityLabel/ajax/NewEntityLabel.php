<?php
class movio_modules_ontologybuilder_controllers_entityLabel_ajax_NewEntityLabel extends org_glizy_mvc_core_CommandAjax
{
    function execute()
    {
        $it = org_glizy_objectFactory::createModelIterator('movio.modules.ontologybuilder.models.Languages', 'all');

        $translation = array();

        foreach($it as $ar) {
            $code = $ar->language_code;
            $word = __Request::get('translation_'.$code);
            $translation[$code] = $word;
            if ($word != '') {
                $key = $word;
            }
        }

        $label = org_glizy_objectFactory::createModel('movio.modules.ontologybuilder.models.EntityLabelsDocument');
        $label->key = $key;
        $label->translation = $translation;
        $id = $label->save();

        $localeService = $this->application->retrieveProxy('movio.modules.ontologybuilder.service.LocaleService');
        $localeService->invalidate();

        return $id;
    }
}
?>