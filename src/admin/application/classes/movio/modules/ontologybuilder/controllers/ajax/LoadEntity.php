<?php
class movio_modules_ontologybuilder_controllers_ajax_LoadEntity extends org_glizy_mvc_core_CommandAjax
{
    function execute($entityId)
    {
        $entityModel = org_glizy_objectFactory::createModel('movio.modules.ontologybuilder.models.Entity');
        $entityModel->load($entityId);
        
        $language = $this->application->getEditingLanguage();

        $localeService = $this->application->retrieveProxy('movio.modules.ontologybuilder.service.LocaleService');

        $entity = array(
            "id" => $entityModel->entity_id,
            "nameId" => $entityModel->entity_name,
            "nameText" => $localeService->getTranslation($language, $entityModel->entity_name),
            "showRelationsGraph" => $entityModel->entity_show_relations_graph,
        );

        $it = org_glizy_objectFactory::createModelIterator('movio.modules.ontologybuilder.models.Entity');
        $it->load('allButNot', array('entityId' => $entityId));

        $entity["entities"] = array();

        foreach($it as $ar) {
            $entity["entities"][] = array(
                "id" => $ar->entity_id,
                "name" => $localeService->getTranslation($language, $ar->entity_name)
            );
        }

        $it = org_glizy_objectFactory::createModelIterator('movio.modules.ontologybuilder.models.EntityProperties');
        $it->load('entityPropertiesFromId', array('entityId' => $entityId));

        $entity["properties"] = array();
        
        foreach($it as $ar) {
            
            $entity["properties"][] = array(
                "id" => $ar->entity_properties_id,
                "type" => $ar->entity_properties_type,
                "target" => $ar->entity_properties_target_FK_entity_id,
                "labelId" => $ar->entity_properties_label_key,
                "labelText" => $localeService->getTranslation($language, $ar->entity_properties_label_key),
                "required" => $ar->entity_properties_required == 1,
                "showLabelInFrontend" => $ar->entity_properties_show_label_in_frontend == 1,
                "relationShow" => $ar->entity_properties_relation_show,
                "dcField" => $ar->entity_properties_dublic_core,
                "rowIndex" => $ar->entity_properties_row_index,
                'params' => $ar->entity_properties_params
            );
        }

        $it = org_glizy_objectFactory::createModelIterator('movio.modules.ontologybuilder.models.EntityProperties');
        $it->load('entityRelationsFromId', array('entityId' => $entityId));

        $entity["relations"] = array();

        foreach($it as $ar) {
            $entity["relations"][] = array(
                "id" => $ar->entity_properties_id,
                "from" => $localeService->getTranslation($language, $ar->entity_name),
                "to" => $localeService->getTranslation($language, $entity["nameText"]),
                "show" => $ar->entity_properties_reference_relation_show
            );
        }

        return $entity;
    }
}