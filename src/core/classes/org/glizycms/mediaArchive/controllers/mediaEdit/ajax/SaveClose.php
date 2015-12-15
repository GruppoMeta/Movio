<?php
class org_glizycms_mediaArchive_controllers_mediaEdit_ajax_SaveClose extends org_glizycms_mediaArchive_controllers_mediaEdit_ajax_Save
{
    public function execute($data)
    {
        $result = parent::execute($data);
        
        if ($result['errors']) {
            return $result;
        }
        else {
            $this->directOutput = true;
            return array('url' => $this->changeAction(''));
        }
    }
}