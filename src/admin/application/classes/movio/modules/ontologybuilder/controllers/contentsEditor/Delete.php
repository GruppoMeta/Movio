<?php
class movio_modules_ontologybuilder_controllers_contentsEditor_Delete extends org_glizy_mvc_core_Command
{
    public function execute($entityId)
    {
        $this->checkPermissionForBackend();
        if ($entityId) {
            $document = org_glizy_objectFactory::createModel('movio.modules.ontologybuilder.models.EntityDocument');
            $document->delete($entityId);
            org_glizy_helpers_Navigation::goHere();
        }
    }
}