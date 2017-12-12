<?php
class movio_modules_ontologybuilder_controllers_contentsEditor_Togglevisibility extends org_glizy_mvc_core_Command
{
    public function execute($entityId)
    {
        $this->checkPermissionForBackend();
        if ($entityId) {
            $document = org_glizy_objectFactory::createModel('movio.modules.ontologybuilder.models.EntityDocument');
            $document->load($entityId);
            $document->setVisible($document->isVisible() ? 0 : 1);
            $document->publish();
            org_glizy_helpers_Navigation::goHere();
        }
    }
}