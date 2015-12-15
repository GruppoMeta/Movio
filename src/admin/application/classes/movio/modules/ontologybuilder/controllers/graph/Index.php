<?php
class movio_modules_ontologybuilder_controllers_graph_Index extends org_glizy_mvc_core_Command
{
    public function execute() 
    {
        $entityTypeId = $this->view->_content->entitySelect;
        __Request::set('entityTypeId', $entityTypeId);
    }
}