<?php
// TODO
// implementare il tag relation nel compiler del model ed elimare questo provvisioro

class org_glizycms_contents_models_DocumentACL extends org_glizy_dataAccessDoctrine_ActiveRecord {

    function __construct($connectionNumber=0) {
        parent::__construct($connectionNumber);
        $this->setTableName('documents_tbl', org_glizy_dataAccessDoctrine_DataAccess::getTablePrefix($connectionNumber));
        
        $sm = new org_glizy_dataAccessDoctrine_SchemaManager($this->connection);
        $sequenceName = $sm->getSequenceName($this->getTableName());
        $this->setSequenceName($sequenceName);
        
        $fields = $sm->getFields($this->getTableName());
        
        foreach ($fields as $field) {
            $this->addField($field);
        }
        
        $this->addRelation(array('type' => 'joinTable', 'name' => 'rel_aclEdit', 'className' => 'org.glizy.models.JoinDoctrine', 'field' => 'join_FK_source_id', 'destinationField' => 'join_FK_dest_id',  'bindTo' => '__aclEdit', 'objectName' => ''));
        $this->addRelation(array('type' => 'joinTable', 'name' => 'rel_aclView', 'className' => 'org.glizy.models.JoinDoctrine', 'field' => 'join_FK_source_id', 'destinationField' => 'join_FK_dest_id',  'bindTo' => '__aclView', 'objectName' => ''));
        $this->setProcessRelations(true);
    }
}
