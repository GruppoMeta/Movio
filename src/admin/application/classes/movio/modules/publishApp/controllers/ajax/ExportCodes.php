<?php
class movio_modules_publishApp_controllers_ajax_ExportCodes extends org_glizy_mvc_core_CommandAjax
{
    function execute()
    {
        if ($this->user->isLogged())
        {
            $it = org_glizy_objectFactory::createModelIterator('movio.modules.codes.models.Model');
       
            foreach ($it as $ar) {
                if (!$ar->custom_code_mapping_code) continue;
                
                $arCode = org_glizy_objectFactory::createModel('movio.modules.publishApp.models.Codes');
                $arCode->mobilecode_code = $ar->custom_code_mapping_code;
                $arCode->mobilecode_link = $ar->custom_code_mapping_link;
                $arCode->save();
            }
        }
    }
}