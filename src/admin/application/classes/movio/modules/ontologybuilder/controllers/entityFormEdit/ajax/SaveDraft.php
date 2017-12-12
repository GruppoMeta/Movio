<?php
class movio_modules_ontologybuilder_controllers_entityFormEdit_ajax_SaveDraft extends org_glizy_mvc_core_CommandAjax
{
    function execute($data)
    {
        $this->checkPermissionForBackend();
        
        $entityProxy = org_glizy_objectFactory::createObject('movio.modules.ontologybuilder.models.proxy.EntityProxy');
        $id = $entityProxy->saveContent(json_decode($data), false);

        $this->directOutput = true;

        // se ci sono stati errori di validazione
        if (is_array($id)) {
            return array('errors' => $id);
        }
        else {
            return array('set' => array('entityId' => $id));
        }
    }
}