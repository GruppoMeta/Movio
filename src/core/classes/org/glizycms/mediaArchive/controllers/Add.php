<?php
class org_glizycms_mediaArchive_controllers_Add extends org_glizy_mvc_core_Command
{
    public function execute()
    {
        $this->checkPermissionForBackend();
        
        $c = $this->view->getComponentById('fileuploader');
        $c->setAttribute('data', ';maxlabel='.ini_get('upload_max_filesize').'B', true);

        if (!__Config::get('glizycms.mediaArchive.mediaMappingEnabled')) {
            $c = $this->view->getComponentById('addFromServer');
            if ($c) {
                $c->setAttribute('label', null);
                $c->setAttribute('visible', false);
            }
        }

        if (!__Config::get('glizycms.mediaArchive.addFromZipEnabled')) {
            $c = $this->view->getComponentById('addFromZip');
            if ($c) {
                $c->setAttribute('label', null);
                $c->setAttribute('visible', false);
            }
        }

    }
}


