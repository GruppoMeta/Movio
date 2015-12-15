<?php
class org_glizycms_mediaArchive_controllers_mediaEdit_Edit extends org_glizy_mvc_core_Command
{
    public function execute()
    {
        $id = __Request::get('id');
        $media = org_glizy_ObjectFactory::createModel('org.glizycms.models.Media');
        $media->load($id);
        $data = $media->getValuesAsArray();

        $this->view->setData($data);
    }
}