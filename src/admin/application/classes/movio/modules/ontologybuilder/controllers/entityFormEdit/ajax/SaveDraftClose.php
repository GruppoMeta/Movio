<?php
class movio_modules_ontologybuilder_controllers_entityFormEdit_ajax_SaveDraftClose extends movio_modules_ontologybuilder_controllers_entityFormEdit_ajax_SaveDraft
{
    function execute($data)
    {
        $this->checkPermissionForBackend();
        
        parent::execute($data);
        return array('url' => $this->changeAction(''));
    }
}
