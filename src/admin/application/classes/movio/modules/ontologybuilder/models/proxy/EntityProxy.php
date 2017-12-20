<?php
class movio_modules_ontologybuilder_models_proxy_EntityProxy extends GlizyObject
{
    public function loadContent($entityId, $status='PUBLISHED')
    {
        $data = array();

        if ($entityId == 0) {
            return array('__isVisible' => '1');
        }

        $document = org_glizy_objectFactory::createObject('org.glizy.dataAccessDoctrine.ActiveRecordDocument');

        $result = $document->load($entityId, $status);

        if (!$result) {
            $languageProxy = __ObjectFactory::createObject('org.glizycms.languages.models.proxy.LanguagesProxy');
            $document->load($entityId, $status, $languageProxy->getDefaultLanguageId());
        }

        $data['__isVisible'] = $document->isVisible();

        $document->addField(new org_glizy_dataAccessDoctrine_DbField('title', Doctrine\DBAL\Types\Type::STRING, 255, false, null,'', false));
        $data['title'] = $document->title;

        $document->addField(new org_glizy_dataAccessDoctrine_DbField('subtitle', Doctrine\DBAL\Types\Type::STRING, 255, false, null,'', false));
        $data['subtitle'] = $document->subtitle;

        $document->addField(new org_glizy_dataAccessDoctrine_DbField('url', Doctrine\DBAL\Types\Type::STRING, 255, false, null, '', true, false, '', org_glizy_dataAccessDoctrine_DbField::NOT_INDEXED));
        $data['url'] = $document->url;

        $document->addField(new org_glizy_dataAccessDoctrine_DbField('profile', Doctrine\DBAL\Types\Type::TARRAY, 255, false, null, '', false));
        $data['profile'] = $document->profile;

        $document->addField(new org_glizy_dataAccessDoctrine_DbField(
                            'externalId',
                            Doctrine\DBAL\Types\Type::INTEGER,
                            10,
                            false,
                            null,
                            '',
                            false));
        $data['externalId'] = $document->externalId;

        $application = org_glizy_ObjectValues::get('org.glizy', 'application' );
        $fieldTypeService = $application->retrieveProxy('movio.modules.ontologybuilder.service.FieldTypeService');

        $documentEntityTypeId = str_replace('entity', '', $document->getType());
        $data['entityTypeId'] = $documentEntityTypeId;

        $entityTypeService = $application->retrieveProxy('movio.modules.ontologybuilder.service.EntityTypeService');
        $entityTypeProperties = $entityTypeService->getEntityTypeProperties($documentEntityTypeId);

        foreach((array) $entityTypeProperties as $entityTypeProperty) {
            $attribute = $entityTypeService->getAttributeIdByProperties($entityTypeProperty);

            // se l'attributo non è una relazione
            if (is_null($entityTypeProperty['entity_properties_target_FK_entity_id'])) {
                $type = $fieldTypeService->getTypeMapping($entityTypeProperty['entity_properties_type']);

                if ($fieldTypeService->isTypeIndexed($entityTypeProperty['entity_properties_type']) == true) {
                    $document->addField(new org_glizy_dataAccessDoctrine_DbField($attribute, $type, 255, false, null,'', false));
                }
                else {
                    $document->addField(new org_glizy_dataAccessDoctrine_DbField($attribute, $type, 255, false, null, '', false, false, '', org_glizy_dataAccessDoctrine_DbField::NOT_INDEXED));
                }

                $value = $document->$attribute;
                if (is_object($value) && property_exists($value, '__restore')) {
                    foreach($value->__restore as $k=>$v) {
                        $data[$k] = $v;
                    }
                } else {
                    $data[$attribute] = $value;
                }
            }
            // se l'attributo è una relazione;
            else {
                $document->addField(new org_glizy_dataAccessDoctrine_DbField($attribute, Doctrine\DBAL\Types\Type::TEXT, 1000, false, null, '', false, false, '', org_glizy_dataAccessDoctrine_DbField::INDEXED));

                $relations = array();

                if ($document->$attribute != '') {
                    foreach ($document->$attribute as $toEntityId) {
                        $title = $this->getEntityTitle($toEntityId);
                        if ($title) {
                            $relations[] = array(
                              'id' => $toEntityId,
                              'text' => $title
                            );
                        }
                    }
                }

                $data[$attribute] = $relations;
            }
        }

        return $data;
    }

    public function saveContent($data, $publish=true)
    {
        $entityTypeId = $data->entityTypeId;
        $entityId = $data->entityId;

        $document = org_glizy_objectFactory::createObject('org.glizy.dataAccessDoctrine.ActiveRecordDocument');

        $document->setType('entity'.$entityTypeId);

        $result = $document->load($entityId, 'LAST_MODIFIED');

        if (!$result) {
            $languageProxy = __ObjectFactory::createObject('org.glizycms.languages.models.proxy.LanguagesProxy');
            $defaultLanguageId = $languageProxy->getDefaultLanguageId();
            $document->load($entityId, 'LAST_MODIFIED', $defaultLanguageId);
            $document->setDetailFromLanguageId($languageProxy->getLanguageId());
        }

        $document->setVisible($data->__isVisible);

        $document->addField(new org_glizy_dataAccessDoctrine_DbField('title', Doctrine\DBAL\Types\Type::STRING, 255, false, null,'', false));
        $document->title = $data->title;

        $document->addField(new org_glizy_dataAccessDoctrine_DbField('subtitle', Doctrine\DBAL\Types\Type::STRING, 255, false, null,'', false));
        $document->subtitle = $data->subtitle;

        $document->addField(new org_glizy_dataAccessDoctrine_DbField('url', Doctrine\DBAL\Types\Type::STRING, 255, false, null, '', false, false, '', org_glizy_dataAccessDoctrine_DbField::NOT_INDEXED));
        $originalUrl = $document->url;
        $document->url = $data->url;

        $document->addField(new org_glizy_dataAccessDoctrine_DbField('profile', Doctrine\DBAL\Types\Type::TARRAY, 255, false, null, '', false));
        $document->profile = $data->profile;

        $document->addField(new org_glizy_dataAccessDoctrine_DbField(
                            'externalId',
                            Doctrine\DBAL\Types\Type::INTEGER,
                            10,
                            false,
                            null,
                            '',
                            false));
        $document->externalId = $data->externalId;

        $application = org_glizy_ObjectValues::get('org.glizy', 'application' );
        $fieldTypeService = $application->retrieveProxy('movio.modules.ontologybuilder.service.FieldTypeService');

        $entityTypeService = $application->retrieveProxy('movio.modules.ontologybuilder.service.EntityTypeService');
        $entityTypeProperties = $entityTypeService->getEntityTypeProperties($entityTypeId);

        $fulltext = $document->title.org_glizycms_Glizycms::FULLTEXT_DELIMITER;

        if ($document->subtitle) {
            $fulltext .= $document->subtitle.org_glizycms_Glizycms::FULLTEXT_DELIMITER;
        }

        foreach((array)$entityTypeProperties as $entityTypeProperty) {
            $attribute = $entityTypeService->getAttributeIdByProperties($entityTypeProperty);

            if ($entityTypeProperty['entity_properties_type']=='attribute.thesaurus') {
                $indexName = $entityTypeProperty['entity_properties_type'].'.'.$entityTypeProperty['entity_properties_params'];
                $document->addField(new org_glizy_dataAccessDoctrine_DbField(
                            $indexName,
                            org_glizy_dataAccessDoctrine_types_Types::ARRAY_ID,
                            255,
                            false,
                            $entityTypeProperty['entity_properties_required'] ? new org_glizy_validators_NotNull() : null,
                            '',
                            false,
                            false,
                            '',
                            org_glizy_dataAccessDoctrine_DbField::ONLY_INDEX));

               $document->{$indexName} = $data->{$attribute};
            }

            // se l'attributo non è una relazione
            if (is_null($entityTypeProperty['entity_properties_target_FK_entity_id'])) {
                $type = $fieldTypeService->getTypeMapping($entityTypeProperty['entity_properties_type']);

                // TODO permettere di definire in fieldTypes.xml altri validatori per ogni tipo
                if ($entityTypeProperty['entity_properties_required']) {
                    $validator = new org_glizy_validators_NotNull();
                }
                else {
                    $validator = null;
                }

                if ($fieldTypeService->isTypeIndexed($entityTypeProperty['entity_properties_type']) == true) {
                    $document->addField(new org_glizy_dataAccessDoctrine_DbField($attribute, $type, 255, false, $validator, '', false));
                }
                else {
                    $document->addField(new org_glizy_dataAccessDoctrine_DbField($attribute, $type, 255, false, $validator, '', false, false, '', org_glizy_dataAccessDoctrine_DbField::NOT_INDEXED));
                }
            }
            // se l'attributo è una relazione;
            else {
                $document->addField(new org_glizy_dataAccessDoctrine_DbField($attribute, Doctrine\DBAL\Types\Type::TEXT, 1000, false, null, '', false, false, '', org_glizy_dataAccessDoctrine_DbField::INDEXED));
            }

            if (property_exists($data, $attribute)) {
                $value = $data->$attribute;
                $document->$attribute = $value;
            } else {
                // se l'attributo non è nei dati può essere dovuto
                // ad un component custom (es phtogallery) che ha più valori da salvare
                $value = array('__restore' => new StdClass);
                foreach($data as $k=>$v) {
                    if (strpos($k, $attribute)===0) {
                        $value['__restore']->$k = $v;
                    }
                }
                $document->$attribute = $value;
            }

            if ($fieldTypeService->isTypeIndexed($entityTypeProperty['entity_properties_type']) && !is_array($value) && !is_object($value)) {
                $stripped = trim(strip_tags($value));
                if (!is_numeric($value) && strlen($stripped) > org_glizycms_Glizycms::FULLTEXT_MIN_CHAR ) {
                    $fulltext .= $stripped.org_glizycms_Glizycms::FULLTEXT_DELIMITER;
                }
            }
        }

        $document->addField(new org_glizy_dataAccessDoctrine_DbField('fulltext', Doctrine\DBAL\Types\Type::TEXT, 1000, false, null, '', false, false, '', org_glizy_dataAccessDoctrine_DbField::FULLTEXT));
        $document->fulltext = $fulltext;

        try {
            if ($publish) {
                $id = $document->publish();
            } else {
                if (__Config::get('glizycms.content.history')) {
                    $id = $document->saveHistory();
                } else {
                    $id = $document->save();
                }
            }

            $speakingUrlProxy = __Config::get('glizycms.speakingUrl') ? org_glizy_ObjectFactory::createObject('org.glizycms.speakingUrl.models.proxy.SpeakingUrlProxy') : null;

            if ($speakingUrlProxy) {
                $languageId = org_glizy_ObjectValues::get('org.glizy', 'editingLanguageId');

                //valida l'url
                if (!$speakingUrlProxy->validate($document->url, $languageId, $id, 'movio.modules.ontologybuilder.content')) {
                    throw new org_glizy_validators_ValidationException(array('Url non valido perché già utilizzato'));
                } else {
                    $options = array('entityTypeId' => $entityTypeId);
                    // aggiorna l'url parlante
                    $speakingUrlProxy->addUrl($document->url, $languageId, $id, 'movio.modules.ontologybuilder.content', $options);
                    org_glizy_cache_CacheFile::cleanPHP('../cache/');
                }
            }
        }
        catch (org_glizy_validators_ValidationException $e) {
            return $e->getErrors();
        }

        return $id;
    }

    public function loadContentFrontend($entityId)
    {
        if ($entityId == 0) {
            org_glizy_helpers_Navigation::notFound();
        }

        $data = array();

        $document = org_glizy_objectFactory::createObject('org.glizy.dataAccessDoctrine.ActiveRecordDocument');

        $languageProxy = __ObjectFactory::createObject('org.glizycms.languages.models.proxy.LanguagesProxy');
        $r = $document->load($entityId);


        if (!$r) {
            org_glizy_helpers_Navigation::notFound();
        }

        $document->addField(new org_glizy_dataAccessDoctrine_DbField('title', Doctrine\DBAL\Types\Type::STRING, 255, false, null, '', false));
        $data['title'] = $document->title;

        $document->addField(new org_glizy_dataAccessDoctrine_DbField('subtitle', Doctrine\DBAL\Types\Type::STRING, 255, false, null, '', false));
        $data['subtitle'] = $document->subtitle;

        $document->addField(new org_glizy_dataAccessDoctrine_DbField('url', Doctrine\DBAL\Types\Type::STRING, 255, false, null, '', false, false, '', org_glizy_dataAccessDoctrine_DbField::NOT_INDEXED));
        $data['url'] = $document->url;

        $document->addField(new org_glizy_dataAccessDoctrine_DbField('profile', Doctrine\DBAL\Types\Type::TARRAY, 255, false, null, '', false));
        $data['profile'] = $document->profile;

        $application = org_glizy_ObjectValues::get('org.glizy', 'application' );
        $fieldTypeService = $application->retrieveProxy('movio.modules.ontologybuilder.service.FieldTypeService');

        $documentEntityTypeId = str_replace('entity', '', $document->getType());
        $data['entityTypeId'] = $documentEntityTypeId;

        $entityTypeService = $application->retrieveProxy('movio.modules.ontologybuilder.service.EntityTypeService');
        $entityTypeProperties = $entityTypeService->getEntityTypeProperties($documentEntityTypeId);

		$localeService = $application->retrieveProxy('movio.modules.ontologybuilder.service.LocaleService');
        $language = $application->getLanguage();

        $relationId = 0;

        foreach((array) $entityTypeProperties as $entityTypeProperty) {
            $attribute = $entityTypeService->getAttributeIdByProperties($entityTypeProperty);

            // se l'attributo non è una relazione
            if (is_null($entityTypeProperty['entity_properties_target_FK_entity_id'])) {
                $type = $fieldTypeService->getTypeMapping($entityTypeProperty['entity_properties_type']);

                if ($fieldTypeService->isTypeIndexed($entityTypeProperty['entity_properties_type']) == true) {
                    $document->addField(new org_glizy_dataAccessDoctrine_DbField($attribute, $type, 255, false, null, '', false));
                }
                else {
                    $document->addField(new org_glizy_dataAccessDoctrine_DbField($attribute, $type, 255, false, null, '', false, false, '', org_glizy_dataAccessDoctrine_DbField::NOT_INDEXED));
                }

                if ($entityTypeProperty['entity_properties_type'] == 'attribute.internallink') {
                    $speakingUrlManager = $application->retrieveProxy('org.glizycms.speakingUrl.Manager');
                    $value = $speakingUrlManager->makeUrl($document->$attribute);
                } else {
                    $value = $document->$attribute;
                }

                if (is_object($value) &&  property_exists($value, '__restore')) {
                    foreach($value->__restore as $k=>$v) {
                        $data[$k] = $v;
                    }
                } else {
                    $data[$attribute] = $value;
                }
            } else { // se l'attributo è una relazione;
                $document->addField(new org_glizy_dataAccessDoctrine_DbField($attribute, Doctrine\DBAL\Types\Type::TEXT, 1000, false, null, '', false, false, '', org_glizy_dataAccessDoctrine_DbField::INDEXED));

                $relations = array();

                if ($document->$attribute != '') {
                    foreach ($document->$attribute as $toEntityId) {
                        $content = $this->loadContent($toEntityId);
                        $content['document_id'] = $toEntityId;
                        $relations[] = $content;
                    }
                }

                $data[$attribute]['id'] = 'relation'.$relationId++;
                $data[$attribute]['content'] = $relations;
                $data[$attribute]['relation'] = $localeService->getTranslation($language, $entityTypeProperty['entity_properties_label_key']);
            }
        }

        $referenceRelations = $entityTypeService->getEntityTypeReferenceRelations($documentEntityTypeId);

        $relations = array();

        foreach((array) $referenceRelations as $referenceRelation) {
            $entityTypeId = $referenceRelation['entity_properties_FK_entity_id'];
            $attribute = $entityTypeService->getAttributeIdByProperties($referenceRelation);

            $document = org_glizy_objectFactory::createModel('movio.modules.ontologybuilder.models.EntityDocument');
            $document->addField(new org_glizy_dataAccessDoctrine_DbField($attribute, Doctrine\DBAL\Types\Type::TEXT, 1000, false, null, '', false, false, '', org_glizy_dataAccessDoctrine_DbField::NOT_INDEXED));
            $it = $document->createRecordIterator();
            $it->load('getReferenceRelations', array('entityTypeId' => $entityTypeId, 'attribute' => $attribute, 'value' => $entityId));

            foreach ($it as $ar) {
                $content = $ar->getValuesAsArray();
                $content['entityTypeId'] = str_replace('entity', '', $ar->getType());
                $data['__reference_relations']['__'.$ar->getType()]['content'][] = $content;
            }

            if ($it->count() != 0) {
                $type = $ar->getType();
                $relatedEntityName = $entityTypeService->getEntityTypeName(str_replace('entity', '', $type));
                $data['__reference_relations']['__'.$type]['id'] = 'relation'.$relationId;
                $data['__reference_relations']['__'.$type]['relation'] = $relatedEntityName;
                $data['__reference_relations']['__'.$type]['show'] = $referenceRelation['entity_properties_reference_relation_show'];
            }
        }

        return $data;
    }

    public function getRelations($entityId)
    {
        if ($entityId == 0) {
            return array();
        }

        $data = array();

        $document = org_glizy_objectFactory::createObject('org.glizy.dataAccessDoctrine.ActiveRecordDocument');
        $result = $document->load($entityId);

        $data['title'] = $document->title;

        $application = org_glizy_ObjectValues::get('org.glizy', 'application' );
        $fieldTypeService = $application->retrieveProxy('movio.modules.ontologybuilder.service.FieldTypeService');

        $documentEntityTypeId = str_replace('entity', '', $document->getType());

        $entityTypeService = $application->retrieveProxy('movio.modules.ontologybuilder.service.EntityTypeService');
        $entityTypeProperties = $entityTypeService->getEntityTypeProperties($documentEntityTypeId);

        $localeService = $application->retrieveProxy('movio.modules.ontologybuilder.service.LocaleService');
        $language = $application->getLanguage();

        $relations = array();

        foreach((array) $entityTypeProperties as $entityTypeProperty) {
            $attribute = $entityTypeService->getAttributeIdByProperties($entityTypeProperty);

            // se l'attributo è una relazione;
            if (!is_null($entityTypeProperty['entity_properties_target_FK_entity_id'])) {
                $document->addField(new org_glizy_dataAccessDoctrine_DbField($attribute, Doctrine\DBAL\Types\Type::TEXT, 1000, false, null, '', false, false, '', org_glizy_dataAccessDoctrine_DbField::INDEXED));

                if ($document->$attribute != '') {
                    foreach ($document->$attribute as $toEntityId) {
                        $relatedDocument = org_glizy_objectFactory::createObject('org.glizy.dataAccessDoctrine.ActiveRecordDocument');
                        $result = $relatedDocument->load($toEntityId);

                        if ($result == true) {
                            $relation = array(
                                'entityTypeId' => str_replace('entity', '', $relatedDocument->getType()),
                                'document_id' => $toEntityId,
                                'title' => $relatedDocument->title,
                                'relation' => $localeService->getTranslation($language, 'rel:'.$entityTypeProperty['entity_properties_type'])
                            );

                            $relations[] = $relation;
                        }
                    }
                }
            }
        }

        $data['relations'] = $relations;

        $referenceRelations = $entityTypeService->getEntityTypeReferenceRelations($documentEntityTypeId);
        $relations = array();

        foreach((array) $referenceRelations as $referenceRelation) {
            $entityTypeId = $referenceRelation['entity_properties_FK_entity_id'];
            $attribute = $entityTypeService->getAttributeIdByProperties($referenceRelation);

            $document = org_glizy_objectFactory::createModel('movio.modules.ontologybuilder.models.EntityDocument');
            $document->addField(new org_glizy_dataAccessDoctrine_DbField($attribute, Doctrine\DBAL\Types\Type::TEXT, 1000, false, null, '', false, false, '', org_glizy_dataAccessDoctrine_DbField::NOT_INDEXED));
            $it = $document->createRecordIterator();
            $it->load('getReferenceRelations', array('entityTypeId' => $entityTypeId, 'attribute' => $attribute, 'value' => $entityId));

            foreach ($it as $ar) {
                $relation = array(
                    'entityTypeId' => $entityTypeId,
                    'document_id' => $ar->getId(),
                    'title' => $ar->title,
                    'relation' => $localeService->getTranslation($language, $referenceRelation['entity_properties_type'])
                );

                $relations[] = $relation;
            }
        }

        $data['reference_relations']= $relations;

        return $data;
    }

    public function getEntityTitle($entityId)
    {
        $document = org_glizy_objectFactory::createObject('org.glizy.dataAccessDoctrine.ActiveRecordDocument');
        $result = $document->load($entityId);

        if ($result == false) {
            return null;
        } else {
            return $document->title;
        }
    }

    public function findEntities($entityTypeId, $name)
    {
        $document = org_glizy_objectFactory::createObject('org.glizy.dataAccessDoctrine.ActiveRecordDocument');
        $document->addField(new org_glizy_dataAccessDoctrine_DbField('title', Doctrine\DBAL\Types\Type::STRING, 255, false, null,'', false));

        $it = $document->createRecordIterator()
            ->whereTypeIs('entity'.$entityTypeId);

        if ($name) {
            $it->where('title', "%$name%", 'LIKE');
        }

        $entities = array();

        foreach($it as $ar) {
            $entities[] = array(
                'id' => $ar->getId(),
                'text' => $ar->title
            );
        }

        return $entities;
    }

    public function findTerm($fieldName, $model, $query, $term, $proxyParams)
    {
        $document = org_glizy_objectFactory::createObject('org.glizy.dataAccessDoctrine.ActiveRecordDocument');
        $document->addField(new org_glizy_dataAccessDoctrine_DbField($fieldName, Doctrine\DBAL\Types\Type::STRING, 255, false, null,'', false));

        $it = $document->createRecordIterator()
            ->whereTypeIs('entity'.$proxyParams->entityTypeId);

        if ($term != '') {
            $it->where($fieldName, '%'.$term.'%', 'LIKE');
        }

        $it->orderBy($fieldName);

        $result = array();

        foreach($it as $ar) {
            $result[] = array(
                'id' => $ar->$fieldName,
                'text' => $ar->$fieldName
            );
        }

        return $result;
    }

    public function getContentsByProfile($groupId)
    {
        $application = org_glizy_ObjectValues::get('org.glizy', 'application');
        $entityTypeService = $application->retrieveProxy('movio.modules.ontologybuilder.service.EntityTypeService');
        $entityResolver = org_glizy_objectFactory::createObject('movio.modules.ontologybuilder.EntityResolver');

        $arGroup = org_glizy_objectFactory::createModel('org.glizycms.groupManager.models.UserGroup');
        $arGroup->load($groupId);

        $it = org_glizy_objectFactory::createModelIterator('movio.modules.ontologybuilder.models.EntityDocument')
            ->load('All')
            ->where('profile', $arGroup->usergroup_name)
            ->orderBy('title');

        foreach ($it as $ar) {
            $entityTypeId = $entityTypeService->getEntityTypeId($ar->getType());
            $descriptionAttribute = $entityTypeService->getDescriptionAttribute($entityTypeId);
            $arMenu = $entityResolver->getMenuVisibleEntity($entityTypeId);

            if ($arMenu) {
                $result[] = array(
                    'title' => $ar->title,
                    'description' => ($descriptionAttribute && $ar->keyInDataExists($descriptionAttribute)) ? $ar->$descriptionAttribute : '',
                    'url' => __Routing::makeUrl('showEntityDetail', array('pageId' => $arMenu->id, 'entityTypeId' => $entityTypeId,'document_id' => $ar->getId()))
                );
            }
        }

        return $result;
    }
}
