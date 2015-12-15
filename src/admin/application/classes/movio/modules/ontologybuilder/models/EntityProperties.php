<?php
class movio_modules_ontologybuilder_models_EntityProperties extends org_glizy_dataAccessDoctrine_ActiveRecord {

    function __construct($connectionNumber=0) {
        parent::__construct($connectionNumber);
        $this->setTableName('entity_properties_tbl');

        $sm = new org_glizy_dataAccessDoctrine_SchemaManager($this->connection);
        $sequenceName = $sm->getSequenceName($this->getTableName());
        $this->setSequenceName($sequenceName);
        
        $fields = $sm->getFields($this->getTableName());
        
        foreach ($fields as $field) {
            $this->addField($field);
        }
    }

    function query_entityPropertiesFromId($iterator, $entityId) {
        $iterator->select('*')
                 ->where("entity_properties_FK_entity_id = $entityId")
                 ->orderBy('entity_properties_row_index');
    }
    
    function query_entityRelationsFromId($iterator, $entityId) {
        $iterator->select('*')
                 ->join('t1', 'entity_tbl', 'e', $iterator->expr()->eq('t1.entity_properties_FK_entity_id', 'e.entity_id'))
                 ->where("entity_properties_target_FK_entity_id = $entityId")
                 ->orderBy('e.entity_name');
    }
}
