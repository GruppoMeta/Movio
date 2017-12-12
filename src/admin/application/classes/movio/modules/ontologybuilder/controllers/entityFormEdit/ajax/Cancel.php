<?php
class movio_modules_ontologybuilder_controllers_entityFormEdit_ajax_Cancel extends org_glizy_mvc_core_CommandAjax
{
    function execute()
    {
        $this->checkPermissionForBackend();
        
        $this->directOutput = true;
        return array('url' => $this->changeAction(''));
    }
}
