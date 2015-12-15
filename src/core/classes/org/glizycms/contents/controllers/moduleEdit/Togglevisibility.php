<?php
class org_glizycms_contents_controllers_moduleEdit_Togglevisibility extends org_glizy_mvc_core_Command
{
    public function execute($id, $model)
    {
// TODO controllo ACL
        if ($id) {
            $contentproxy = org_glizy_objectFactory::createObject('org.glizycms.contents.models.proxy.ModuleContentProxy');
            $contentproxy->toggleVisibility($id, $model);
            org_glizy_helpers_Navigation::goHere();
        }
    }
}