<?php
class movio_modules_ontologybuilder_models_Languages extends org_glizy_dataAccessDoctrine_ActiveRecord {

    function __construct($connectionNumber=0) {
        parent::__construct($connectionNumber);
        $this->setTableName('languages_tbl');

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
                 ->orderBy('language_order');
    }
}
