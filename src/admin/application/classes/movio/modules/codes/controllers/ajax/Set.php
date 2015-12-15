<?php
class movio_modules_codes_controllers_ajax_Set extends org_glizy_mvc_core_CommandAjax
{
    function execute($pk, $name, $value)
    {
        $ar = org_glizy_objectFactory::createModel('movio.modules.codes.models.Model');
        
        if ($ar->load($pk)) {
            $ar->$name = $value;
            $ar->save();
        }
    }
}