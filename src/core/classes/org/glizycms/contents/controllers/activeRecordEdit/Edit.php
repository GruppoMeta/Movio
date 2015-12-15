<?php
class org_glizycms_contents_controllers_activeRecordEdit_Edit extends org_glizy_mvc_core_Command
{
    public function execute($id)
    {   
        if ($id) {
            $c = $this->view->getComponentById('__model');
            $model = $c->getAttribute('value');
            $proxy = org_glizy_objectFactory::createObject('org.glizycms.contents.models.proxy.ActiveRecordProxy');
            $data = $proxy->load($id, $model);

            $data['__id'] = $id;
            $this->view->setData($data);
        }
    }
}