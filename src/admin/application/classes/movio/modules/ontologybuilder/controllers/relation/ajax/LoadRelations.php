<?php
class movio_modules_ontologybuilder_controllers_relation_ajax_LoadRelations extends org_glizy_mvc_core_CommandAjax
{
    function execute()
    {
// TODO controllo dei permessi
        $it = org_glizy_objectFactory::createModelIterator('movio.modules.ontologybuilder.models.RelationTypesDocument', 'all')
            ->groupBy('simple_document_id')
            ->orderBy('translation');
            
        $result = array();
        foreach($it as $ar) {
            $result["relations"][] = array(
                'id' => $ar->getId(),
                'translation' => $ar->translation,
                'cardinality' => $ar->cardinality
            );
        }

        return $result;
    }
}
?>