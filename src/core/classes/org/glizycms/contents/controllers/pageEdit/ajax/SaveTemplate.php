<?php
class org_glizycms_contents_controllers_pageEdit_ajax_SaveTemplate extends org_glizy_mvc_core_CommandAjax
{
    public function execute($data)
    {
// TODO: controllo acl
        $data = json_decode($data);
        if ($data && property_exists($data, 'template')) {
            $templateProxy = org_glizy_ObjectFactory::createObject('org.glizycms.template.models.proxy.TemplateProxy');
            $templateProxy->setSelectedTemplate($data->template);
            return true;
        }

        return false;
    }
}