<?php
class movio_modules_ontologybuilder_controllers_entityLabel_ajax_LoadEntityLabels extends org_glizy_mvc_core_CommandAjax
{
    function execute()
    {
        $it = org_glizy_objectFactory::createModelIterator('movio.modules.ontologybuilder.models.EntityLabelsDocument', 'all')
            ->groupBy('simple_document_id')
            ->orderBy('translation');

        $result = array(
            "entityLabels" => array()
        );
        
        foreach($it as $ar) {
            $result["entityLabels"][] = array(
                'id' => $ar->getId(),
                'translation' => $ar->translation
            );
        }
        
        return $result;
    }
}
?>