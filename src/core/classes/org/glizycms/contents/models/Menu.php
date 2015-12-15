<?php
// TODO
// implementare il tag relation nel compiler del model ed elimare questo provvisioro

class org_glizycms_contents_models_Menu extends org_glizy_dataAccessDoctrine_ActiveRecord {

    function __construct($connectionNumber=0) {
        parent::__construct($connectionNumber);
        $this->setTableName('menus_tbl', org_glizy_dataAccessDoctrine_DataAccess::getTablePrefix($connectionNumber));
        
        $sm = new org_glizy_dataAccessDoctrine_SchemaManager($this->connection);
        $sequenceName = $sm->getSequenceName($this->getTableName());
        $this->setSequenceName($sequenceName);
        
        $fields = $sm->getFields($this->getTableName());
        
        foreach ($fields as $field) {
            $this->addField($field);
        }
        
        /*
        // TODO far funzionare le relazioni
        
        $this->addRelation(array('type' => 'joinTable', 'name' => 'rel_aclFront', 'className' => 'org.glizy.models.JoinDoctrine', 'field' => 'join_FK_source_id', 'destinationField' => 'join_FK_dest_id',  'bindTo' => 'aclFront', 'objectName' => ''));
        $this->addRelation(array('type' => 'joinTable', 'name' => 'rel_aclBack', 'className' => 'org.glizy.models.JoinDoctrine', 'field' => 'join_FK_source_id', 'destinationField' => 'join_FK_dest_id',  'bindTo' => 'aclBack', 'objectName' => ''));
        $this->setProcessRelations(true);
        */
    }
}
