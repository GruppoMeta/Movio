<?php
class org_glizycms_mediaArchive_controllers_mediaEdit_ajax_Cancel extends org_glizy_mvc_core_CommandAjax
{
    public function execute($data)
    {
        $this->directOutput = true;
        return array('url' => $this->changeAction(''));
    }
}