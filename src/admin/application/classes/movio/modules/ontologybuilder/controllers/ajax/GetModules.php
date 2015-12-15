<?php
class movio_modules_ontologybuilder_controllers_ajax_GetModules extends org_glizy_mvc_core_CommandAjax
{
    function execute()
    {
        $modules = org_glizy_Modules::getModules();
        
        $result = array();
        
        foreach ($modules as $module) {
            if ($module->showInOntologyBuilder) {
                $result[] = array(
                    'id' => $module->id,
                    'name' => $module->name
                );
            }
        }
        
        return $result;
    }
}