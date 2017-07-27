<?php
class org_glizycms_contents_controllers_moduleEdit_ajax_SaveDraft extends org_glizy_mvc_core_CommandAjax
{
    public function execute($data)
    {
        $this->checkPermissionForBackend();
// TODO controllo acl
// TODO per motifi di sicurezza forse è meglio non passare il nome del model nella request
// ma avere un controller specifico che estende quello base al quale viene passato il nome del model, come succede per Scaffold

        $contentproxy = org_glizy_objectFactory::createObject('org.glizycms.contents.models.proxy.ModuleContentProxy');
        $result = $contentproxy->saveContent(json_decode($data), __Config::get('glizycms.content.history'), true);

        $this->directOutput = true;

        if ($result['__id']) {
            return array('set' => $result);
        }
        else {
            return array('errors' => $result);
        }
    }
}