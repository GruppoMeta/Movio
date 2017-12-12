<?php
class movio_modules_ontologybuilder_controllers_entityFormEdit_ajax_SaveClose extends movio_modules_ontologybuilder_controllers_entityFormEdit_ajax_Save
{
    function execute($data)
    {
        $this->checkPermissionForBackend();
        
        parent::execute($data);
        return array('url' => $this->changeAction(''));
    }
}
