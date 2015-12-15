<?php
class org_glizycms_contents_controllers_moduleEdit_ajax_Save extends org_glizy_mvc_core_CommandAjax
{
    public function execute($data)
    {
// TODO controllo acl
// TODO per motifi di sicurezza forse è meglio non passare il nome del model nella request
// ma avere un controller specifico che estende quello base al quale viene passato il nome del model, come succede per Scaffold

        $contentproxy = org_glizy_objectFactory::createObject('org.glizycms.contents.models.proxy.ModuleContentProxy');
        $result = $contentproxy->saveContent(json_decode($data));
        
        $this->directOutput = true;
        
        if ($result['__id']) {
            return array('set' => $result);
        }
        else {
            return array('errors' => $result);
        }
    }
}