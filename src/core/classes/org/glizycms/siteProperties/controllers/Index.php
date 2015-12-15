<?php
class org_glizycms_siteProperties_controllers_Index extends org_glizy_mvc_core_Command
{
    public function execute()
    {
        $siteProp = unserialize(org_glizy_Registry::get(__Config::get('REGISTRY_SITE_PROP').$this->application->getEditingLanguage(), ''));
        if ($siteProp) {
            $this->view->setData($siteProp);
        }
    }
}