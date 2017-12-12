<?php
class org_glizycms_mediaArchive_controllers_Delete extends org_glizy_mvc_core_Command
{
    public function execute($id)
    {
        $this->checkPermissionForBackend();
        $mediaProxy = org_glizy_ObjectFactory::createObject('org.glizycms.mediaArchive.models.proxy.MediaProxy');
        $mediaProxy->deleteMedia($id);  
     	
        org_glizy_helpers_Navigation::goHere();
    }
}