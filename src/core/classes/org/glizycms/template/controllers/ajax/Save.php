<?php
class org_glizycms_template_controllers_ajax_Save extends org_glizy_mvc_core_CommandAjax
{
    public function execute($data)
    {
// TODO: controllo acl
        $templateProxy = org_glizy_ObjectFactory::createObject('org.glizycms.template.models.proxy.TemplateProxy');
        $templateProxy->saveEditData($data);

        return true;
    }
}