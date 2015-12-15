<?php
class org_glizycms_template_controllers_ajax_SaveTemplate extends org_glizy_mvc_core_CommandAjax
{
    public function execute($data)
    {
// TODO: controllo acl
        $data = json_decode($data);
        if ($data && property_exists($data, 'template')) {
            $templateProxy = org_glizy_ObjectFactory::createObject('org.glizycms.template.models.proxy.TemplateProxy');
            $templateProxy->setSelectedTemplate($data->template);
            org_glizy_cache_CacheFile::cleanPHP(__Paths::get('APPLICATION_TO_ADMIN_CACHE'));
            return true;
        }
        return false;
    }
}