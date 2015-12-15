<?php
class movio_modules_ontologybuilder_controllers_ajax_GetFieldTypes extends org_glizy_mvc_core_CommandAjax
{
    function execute()
    {
        $application = org_glizy_ObjectValues::get('org.glizy', 'application');
        $language = $application->getLanguage();

        $fieldTypeService = $this->application->retrieveProxy('movio.modules.ontologybuilder.service.FieldTypeService');
        $allTypes = $fieldTypeService->getAllTypes();
        
        $fieldTypes = array();
        
        foreach($allTypes as $key => $value) {
            $fieldTypes[] = array(
                'id' => $key,
                'name' => $value['translation'][$language]
            );
        }
        
        return $fieldTypes;
    }
}