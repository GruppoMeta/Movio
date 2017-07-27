<?php
class org_glizycms_userManager_controllers_Edit extends org_glizycms_contents_controllers_activeRecordEdit_Edit
{
    public function execute($id)
    {
        if ($id) {
            $c = $this->view->getComponentById('__model');
            $model = $c->getAttribute('value');
            $proxy = org_glizy_objectFactory::createObject('org.glizycms.contents.models.proxy.ActiveRecordProxy');
            $data = $proxy->load($id, $model);

            if (__Config::get('PSW_METHOD')) {
            	$data['user_password'] = '';
            	$this->setComponentsAttribute('user_password', 'type', 'password');
            	$this->setComponentsAttribute('user_password', 'required', 'false');
            }

            $data['__id'] = $id;
            $this->view->setData($data);
        }
    }
}

