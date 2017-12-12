<?php
class movio_modules_ontologybuilder_controllers_entityFormEdit_EditDraft extends org_glizy_mvc_core_Command
{
    public function execute()
    {
        $this->checkPermissionForBackend();
        
        $entityId = __Request::get('entityId');
        // TODO controllare se $entityId == 0
        // se non è 0 e il contenuto non esiste bisogna visualizzare un errore

        $entityProxy = org_glizy_objectFactory::createObject('movio.modules.ontologybuilder.models.proxy.EntityProxy');
        $data = $entityProxy->loadContent($entityId, 'DRAFT');
        if ($data) $data['__id'] = $entityId;
        $this->view->setData($data);
    }
}