<?php

abstract class org_glizy_dataAccessDoctrine_AbstractRelation extends GlizyObject implements org_glizy_dataAccessDoctrine_interfaces_RelationInterface
{
    protected $key = '';
    protected $destinationKey = '';
    protected $parent;
    protected $className = '';
    protected $record = null;
    
    function __construct($parent, $options)
    {
        $this->parent = $parent;
        assert(isset($options['className']));
        $this->className = $options['className'];
    }
    
    public function preSave()
    {
    }
    
    public function postSave() 
    {
    }
    
    public function delete() 
    {
    }
}