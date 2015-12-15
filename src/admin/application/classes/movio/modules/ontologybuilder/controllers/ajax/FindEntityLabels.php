<?php
class movio_modules_ontologybuilder_controllers_ajax_FindEntityLabels extends org_glizy_mvc_core_CommandAjax
{
    function execute($term)
    {
        $application = org_glizy_ObjectValues::get('org.glizy', 'application');
        $language = $application->getEditingLanguage();

        if (is_null($term)) {
            $it = org_glizy_objectFactory::createModelIterator('movio.modules.ontologybuilder.models.EntityLabelsDocument');
            $it->load('entityLabelsFromLanguage', array('language' => $language));
        } else {
            $params = array(
                'language' => $language,
                'term' => $term
            );
            $it = org_glizy_objectFactory::createModelIterator('movio.modules.ontologybuilder.models.EntityLabelsDocument');
            $it->load('entityLabelsFromTerm', $params);
        }

        $labels = array();

        //org_glizy_dataAccessDoctrine_DataAccess::enableLogging();
        foreach($it as $ar) {
            $labels[] = array(
                'id' => $ar->key,
                'text' => $ar->translation[$language]
            );
        }

        //die;

        return $labels;
    }
}
?>