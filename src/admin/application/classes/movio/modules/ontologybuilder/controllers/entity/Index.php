<?php
class movio_modules_ontologybuilder_controllers_entity_Index extends org_glizy_mvc_core_Command
{
    public function execute()
    {
        $entityTypeId = $this->view->_content->entitySelect;
        $visualization = $this->view->loadContent('visualization');

        __Request::set('entityTypeId', $entityTypeId);
        $this->setComponentsAttribute('list', 'entityTypeId', $entityTypeId);
        $this->setComponentsAttribute('list', 'visualization', $visualization);

        $showForm = $this->view->loadContent('showForm');
        if ('false'===$showForm) {
            $this->setComponentsVisibility('filters', false);
            $this->setComponentsAttribute('list', 'title', '');
        }
    }
}