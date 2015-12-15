<?php
class org_glizycms_languages_controllers_Delete extends org_glizy_mvc_core_Command
{
    public function executeLater($id)
    {
// TODO controllo ACL
        if ($id) {
            $proxy = org_glizy_objectFactory::createObject('org.glizycms.languages.models.proxy.LanguagesProxy');
            $proxy->delete($id);

            org_glizy_helpers_Navigation::goHere();
        }
    }
}