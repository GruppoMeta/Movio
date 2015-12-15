<?php
class org_glizycms_template_controllers_CheckTemplateTabDraw extends org_glizy_mvc_core_Command
{
    public function execute($menuId)
    {
        $templateEnabled = __Config::get('glizycms.contents.templateEnabled');
        if ($templateEnabled) {
            $templateProxy = org_glizy_ObjectFactory::createObject('org.glizycms.template.models.proxy.TemplateProxy');
            $templateEnabled = $templateProxy->getTemplateAdmin()!==false;
        }
        $this->view->setAttribute('draw', $templateEnabled);
    }
}
