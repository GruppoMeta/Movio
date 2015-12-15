<?php
class org_glizycms_modulesManager_controllers_Duplicate extends org_glizy_mvc_core_Command
{
    public function execute($id, $moduleId)
    {
// TODO tradurre le label

        if (!$moduleId) {
            $this->setComponentsAttribute('moduleId', 'value', $id);
            $moduleVO = $this->getModuleVO($id);
            $text = '<p>Duplicazione modulo <b>'.__T($moduleVO->name).'</b> id: '.$moduleVO->id.'<p>';
            $this->setComponentsAttribute('text', 'text', $text);
        } else {
            $moduleVO = $this->getModuleVO($moduleId);
            $duplicateClass = org_glizy_ObjectFactory::createObject($moduleVO->classPath.'.Duplicate', $moduleVO);


            org_glizy_Modules::deleteCache();
            $this->changeAction('index');
        }
    }

    private function getModuleVO($id)
    {
// TODO controllare che il modulo sia corretto
        $modules = org_glizy_Modules::getModules();
        return $modules[$id];
    }
}