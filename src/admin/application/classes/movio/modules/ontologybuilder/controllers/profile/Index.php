<?php
class movio_modules_ontologybuilder_controllers_profile_Index extends org_glizy_mvc_core_Command
{
    public function execute()
    {
        $groupId = $this->view->_content->groupId;
        if (!$groupId) {
            if ($this->user->isLogged()) {
                $groupId = $this->user->groupId;
            }
        }

        if ($groupId) {
             $this->setComponentsAttribute('loginCheck', 'enabled', false);
        }

        __Request::set('groupId', $groupId);
    }
}