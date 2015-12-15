<?php
class movio_modules_ontologybuilder_controllers_ajax_SaveEntity extends org_glizy_mvc_core_CommandAjax
{
    private $entityTypeService;

    function execute($entity)
    {
        $resultEntity = array();
        $resultEntity['newProperties'] = array();

        $entityModel = org_glizy_objectFactory::createModel('movio.modules.ontologybuilder.models.Entity');
        $entityModel->load($entity['id']);
        $entityModel->entity_name = $entity['name'];
        $entityModel->entity_show_relations_graph = $entity['showRelationsGraph'] == 'true' ? 1 : 0;

        // se è una nuova entità
        if ($entity['id'] == '') {
            $id = $entityModel->save();
            $resultEntity['id'] = $entity['id'] = $id;
        }

        $entityProperties = org_glizy_objectFactory::createModel('movio.modules.ontologybuilder.models.EntityProperties');
        // elimina dal db le proprietà cancellate dall'utente
        foreach ((array) $entity['deletedRows'] as $id) {
            $entityProperties->delete($id);
        }

        // aggiorna i dati delle proprietà nel db
        for ($i = 0; $i < count($entity['properties']); $i++) {
            $property = $entity['properties'][$i];
            $entityProperties->load($property['id']);
            $entityProperties->entity_properties_FK_entity_id = $entity['id'];
            $entityProperties->entity_properties_type = $property['type'];
            $entityProperties->entity_properties_target_FK_entity_id = $this->checkNull($property['target']);
            $entityProperties->entity_properties_label_key = $property['label'];
            $entityProperties->entity_properties_required = $property['required'] == 'true' ? 1 : 0;
            $entityProperties->entity_properties_show_label_in_frontend = $property['showLabelInFrontend'] == 'true' ? 1 : 0;
            $entityProperties->entity_properties_relation_show = $this->checkNull($property['relationShow']);
            $entityProperties->entity_properties_dublic_core = $this->checkNull($property['dcField']);
            $entityProperties->entity_properties_row_index = $this->checkNull($property['rowIndex']);
            $entityProperties->entity_properties_params = $this->checkNull($property['params']);
            $id = $entityProperties->save();

            // se è una nuova proprietà
            if ($property['id'] == '') {
                $entity['properties'][$i]['id'] = $id;
                $resultEntity['newProperties'][] = $id;
            }
        }

        foreach ((array) $entity['relations'] as $relation) {
            $entityProperties->load($relation['id']);
            $entityProperties->entity_properties_reference_relation_show = $relation['show'];
            $entityProperties->save();
        }

        $this->entityTypeService = $this->application->retrieveProxy('movio.modules.ontologybuilder.service.EntityTypeService');
        $this->entityTypeService->invalidate();

        //
        $skinAttributesVO = org_glizy_objectFactory::createObject('movio.modules.ontologybuilder.models.vo.SkinAttributesVO');
        $this->saveSkin($skinAttributesVO, $entity);
        $this->saveListSkin($skinAttributesVO, $entity);

        $entityModel->entity_skin_attributes = serialize($skinAttributesVO);
        $entityModel->save();

        return $resultEntity;
    }

    private function checkNull($value)
    {
        return $value !== '' ? $value : null;
    }

    private function saveSkin($skinAttributesVO, $entity)
    {
        $figure = array();
        $outsideDL = array();
        $insideDL = array();

        $properties = (array) $entity['properties'];
        ksort($properties);
        foreach ($properties as $property) {
            // se la proprietà non è una relazione
            if ($property['target'] == '') {
                $attribute = $this->entityTypeService->getAttributeIdById($property['id']);
                $showLabel = $property['showLabelInFrontend'] == 'true';
                $label = $property['label'];
                $type = $property['type'];

                switch ($type) {
                    case 'attribute.longtext':
                    case 'attribute.descriptivetext':
                        $outsideDL[] = array('type' => 'longtext', 'name' => $attribute,
                                'label' => ($property['dcField'] != 'DC.Description' && $showLabel) ? $label : ''
                                );
                        break;

                    case 'attribute.image':
                    case 'attribute.media':
                    case 'attribute.externalimage':
                        if ($type == 'attribute.image' && count($figure)==0) {
                            $figure[] = array('type' => 'image', 'name' => $attribute, 'label' => '');
                        }
                        else {
                            $outsideDL[] = array('type' => 'media', 'name' => $attribute, 'label' => $showLabel ? $label : '');
                        }
                        break;

                    case 'attribute.imagelist':
                        $outsideDL[] = array('type' => 'imagelist', 'name' => $attribute, 'label' => $showLabel ? $label : '');
                        break;

                    case 'attribute.medialist':
                        $outsideDL[] = array('type' => 'medialist', 'name' => $attribute, 'label' => $showLabel ? $label : '');
                        break;

                    case 'attribute.date':
                        $insideDL[] = array('type' => 'date', 'name' => $attribute, 'label' => $showLabel ? $label : '');
                        break;

                    case 'attribute.thesaurus':
                        $insideDL[] = array('type' => 'thesaurus', 'name' => $attribute, 'label' => $showLabel ? $label : '');
                        break;
                        
                    case 'attribute.module':
                        $insideDL[] = array('type' => 'module', 'name' => $attribute, 'label' => $showLabel ? $label : '');
                        break;

                    default:
                        $insideDL[] = array('type' => 'property', 'name' => $attribute, 'label' => $showLabel ? $label : '');
                }
            }
        }

        $skinAttributesVO->setDetailAttributes($insideDL, $figure, $outsideDL);
    }

    private function saveListSkin($skinAttributesVO, $entity)
    {
        $attribute = false;
        // cerca l'attributo con dcField uguale a DC.Description
        foreach ((array) $entity['properties'] as $property) {
            if ($property['dcField'] == 'DC.Description') {
                $attribute = $this->entityTypeService->getAttributeIdById($property['id']);
                break;
            }
        }

        if (!$attribute) {
            // cerca il primo attributo disponibile che non sia una relazione
            foreach ((array) $entity['properties'] as $property) {
                // verifica che l'attributo non sia una relazione e che sia testuale
                if ($property['target'] == '' && preg_match('/text$/', $property['type'])) {
                    $attribute = $this->entityTypeService->getAttributeIdById($property['id']);
                    break;
                }
            }
        }

        if ($attribute) {
            $skinAttributesVO->setDescriptionAttribute($attribute);
        }
    }
}
