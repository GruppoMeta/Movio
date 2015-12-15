<?php
class movio_modules_siteProperties_controllers_ajax_Save extends org_glizycms_siteProperties_controllers_ajax_Save
{
    public function execute($data)
    {
        $data = json_decode($data);
        $newData = array();
        $newData['title'] = $data->title;
        $newData['subtitle'] = $data->subtitle;
        $newData['address'] = $data->address;
        $newData['copyright'] = $data->copyright;
        $newData['slideShow'] = $data->slideShow;
        $newData['analytics'] = $data->analytics;
        $newData['googleMapsApiKey'] = $data->googleMapsApiKey;

        org_glizy_Registry::set(__Config::get('REGISTRY_SITE_PROP').$this->application->getEditingLanguage(), serialize($newData));
        return true;
    }
}