<?php
class movio_modules_ontologybuilder_models_Entity extends org_glizy_dataAccessDoctrine_ActiveRecord {

    function __construct($connectionNumber=0) {
        parent::__construct($connectionNumber);
        $this->setTableName('entity_tbl');

        $sm = new org_glizy_dataAccessDoctrine_SchemaManager($this->connection);
        $sequenceName = $sm->getSequenceName($this->getTableName());
        $this->setSequenceName($sequenceName);
        
        $fields = $sm->getFields($this->getTableName());
        
        foreach ($fields as $field) {
            $this->addField($field);
        }
    }
    
    function query_all($iterator) {
        $iterator->select('*')
                 ->orderBy('entity_name');
    }
    
    function query_allButNot($iterator, $entityId) {
        $iterator->select('*')
                 ->where("entity_id <> $entityId")
                 ->orderBy('entity_name');
    }
    
    function query_allWithProperties($iterator) {
     		$iterator->select('*')
                 ->leftJoin('t1', 'entity_properties_tbl', 'e',  $iterator->expr()->eq('t1.entity_id', 'e.entity_properties_FK_entity_id'))
                 ->orderBy('entity_properties_row_index');
    }
}
