<?php
class org_glizycms_mediaArchive_controllers_ajax_Delete extends org_glizy_mvc_core_CommandAjax
{
    public function execute($data)
    {
        $ids = explode(',', __Request::get('ids'));
        if (!empty($ids)) {
            $mediaProxy = org_glizy_ObjectFactory::createObject('org.glizycms.mediaArchive.models.proxy.MediaProxy');
            foreach ($ids as $id) {
                $mediaProxy->deleteMedia($id);
            }
        }
    }
}