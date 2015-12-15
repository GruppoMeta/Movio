<?php
class movio_modules_ontologybuilder_controllers_ajax_GetLanguages extends org_glizy_mvc_core_CommandAjax
{
    function execute()
    {
        $it = org_glizy_objectFactory::createModelIterator('movio.modules.ontologybuilder.models.Languages', 'all');

        $languages = array();

        foreach($it as $ar) {
            $languages[] = array(
                'language_name' => $ar->language_name,
                'language_code' => $ar->language_code
            );
        }

        return $languages;
    }
}
?>