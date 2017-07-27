<?php
class org_glizycms_contents_controllers_moduleEdit_EditDraft extends org_glizy_mvc_core_Command
{
    public function execute($id)
    {
        $this->checkPermissionForBackend();
// TODO controllo ACL
        if ($id) {
            // read the module content
            $c = $this->view->getComponentById('__model');
            $contentproxy = org_glizy_objectFactory::createObject('org.glizycms.contents.models.proxy.ModuleContentProxy');
            $data = $contentproxy->loadContent($id, $c->getAttribute('value'), 'DRAFT');
//  TODO verifica se il record esiste
            $data['__id'] = $id;
            $this->view->setData($data);
        }
    }
}