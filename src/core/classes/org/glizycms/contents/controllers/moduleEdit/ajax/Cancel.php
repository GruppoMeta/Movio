<?php
class org_glizycms_contents_controllers_moduleEdit_ajax_Cancel extends org_glizy_mvc_core_CommandAjax
{
    public function execute($data)
    {
        $this->checkPermissionForBackend();
        $this->directOutput = true;
        return array('url' => $this->changeAction(''));
    }
}
