<?php
class movio_modules_siteProperties_controllers_Index extends org_glizycms_siteProperties_controllers_Index
{
    public function execute()
    {
        $siteProp = unserialize(org_glizy_Registry::get(__Config::get('REGISTRY_SITE_PROP').$this->application->getEditingLanguage(), ''));
        if ($siteProp) {
            $this->view->setData($siteProp);
        }
    }
}