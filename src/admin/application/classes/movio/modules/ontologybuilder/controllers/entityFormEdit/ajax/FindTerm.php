<?php
class movio_modules_ontologybuilder_controllers_entityFormEdit_ajax_FindTerm extends org_glizy_mvc_core_CommandAjax
{
    function execute($fieldName, $model, $query, $term)
    {
        $this->checkPermissionForBackend();
        
        $it = org_glizy_objectFactory::createModelIterator($model, $query);
        $it->where($fieldName, '%'.$term.'%', 'LIKE');

        $results = array();

        foreach($it as $ar) {
            $results[] = array(
                'id' => $ar->getId(),
                'text' => $ar->$fieldName
            );
        }

        return $results;
    }
}
?>