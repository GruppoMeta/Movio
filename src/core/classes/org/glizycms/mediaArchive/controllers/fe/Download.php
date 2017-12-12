<?php
use Hashids\Hashids;

class org_glizycms_mediaArchive_controllers_fe_Download extends org_glizy_mvc_core_Command
{
    public function execute($hash, $filename)
    {
        $hashGenerator = __ObjectFactory::createObject('org.glizy.helpers.HashGenerator');
        $id = $hashGenerator->decode($hash);
        org_glizycms_Glizycms::getMediaArchiveBridge()->serveMedia($id);
    }
}