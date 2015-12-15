<?php
class org_glizycms_contents_controllers_pageEdit_Add extends org_glizy_mvc_core_Command
{
    public function execute($menuId)
    {
        if ($menuId) {
            $this->setComponentsAttribute('pageParent', 'value', $menuId);
        }
    }
}