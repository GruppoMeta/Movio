<?php

interface org_glizy_dataAccessDoctrine_interfaces_RelationInterface 
{
    public function build($params=array());
    public function preSave();
    public function postSave();
    public function delete();
}