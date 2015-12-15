<?php
class org_glizycms_template_controllers_Template extends org_glizy_mvc_core_Command
{
    public function execute($menuId)
    {
        if ($menuId) {
            // read

            $this->view->setData($data);
        } else {
// TODO errore
        }

    }
}