<?php
class movio_modules_thesaurus_controllers_termEdit_Edit extends movio_modules_thesaurus_controllers_termEdit_Add
{
    public function execute()
    {
        $this->checkPermissionForBackend();
        parent::execute();
    }
}