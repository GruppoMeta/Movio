<?php
class org_glizycms_mediaArchive_controllers_AddFromZip extends org_glizycms_mediaArchive_controllers_Add
{
    public function execute()
    {
        parent::execute();
        $this->setComponentsVisibility('media_title', false);
    }
}


