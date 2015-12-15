<?php
class movio_modules_codes_controllers_ajax_Del extends org_glizy_mvc_core_CommandAjax
{
    function execute($id)
    {
// TODO verificare permessi
        $ar = org_glizy_objectFactory::createModel('movio.modules.codes.models.Model');
        $ar->delete($id);

        return true;
    }
}
