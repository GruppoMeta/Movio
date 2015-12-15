<?php
class movio_modules_codes_controllers_ajax_New extends org_glizy_mvc_core_CommandAjax
{
    function execute($custom_code_mapping_description, $custom_code_mapping_code, $custom_code_mapping_link)
    {
        $ar = org_glizy_objectFactory::createModel('movio.modules.codes.models.Model');
        $ar->custom_code_mapping_description = $custom_code_mapping_description;
        $ar->custom_code_mapping_code = $custom_code_mapping_code;
        $ar->custom_code_mapping_link = $custom_code_mapping_link;
        $id = $ar->save();
        return $id;
    }
}
?>