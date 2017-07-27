<?php
class movio_modules_ontologybuilder_service_EntityTypeService extends GlizyObject
{
    protected $entityTypes = array();

	public function __construct()
	{
	    $this->init();
	}

    public function init() {
        $options = array(
			'cacheDir' => org_glizy_Paths::get('CACHE_CODE'),
			'lifeTime' => -1,
			'readControlType' => '',
			'fileExtension' => '.php'
		);
		$cacheObj = &org_glizy_ObjectFactory::createObject('org.glizy.cache.CacheFile', $options);
		$cacheFileName = $cacheObj->verify(get_class( $this ));

		if ( $cacheFileName === false )
		{
			$this->loadData();
            $cacheObj->save( serialize( $this->entityTypes ), NULL, get_class( $this ) );
			$cacheObj->getFileName();
		}
		else
		{
			$this->entityTypes = unserialize( file_get_contents( $cacheFileName ) );
		}
    }

    public function invalidate()
    {
        org_glizy_cache_CacheFile::cleanPHP();
        org_glizy_cache_CacheFile::cleanPHP(__Paths::get( 'BASE' ).'cache/');
        $this->init();
    }

    private function loadData()
    {
        $it = org_glizy_objectFactory::createModelIterator('movio.modules.ontologybuilder.models.RelationTypesDocument', 'all');

        $relations = array();

        foreach($it as $ar) {
            $relations[$ar->key] = array(
                'translation' => $ar->translation,
                'cardinality' => $ar->cardinality
            );
        }

        $this->entityTypes["relations"] = $relations;

        $it = org_glizy_objectFactory::createModelIterator('movio.modules.ontologybuilder.models.Entity', 'allWithProperties');

        $entityTypes = array();
        $descriptionAttributeDc = array();

        foreach($it as $ar) {
            $entityTypes[$ar->entity_id]['name'] = $ar->entity_name;
            $entityTypes[$ar->entity_id]['entity_show_relations_graph'] = $ar->entity_show_relations_graph;
            $entityTypes[$ar->entity_id]['entity_skin_attributes'] = $ar->entity_skin_attributes;

            if ($ar->entity_properties_id) {
                $attributeId = $this->getAttributeIdById($ar->entity_properties_id);

                $entityTypes[$ar->entity_id]['properties'][$attributeId] = array(
                    'entity_properties_id' => $ar->entity_properties_id,
                    'entity_properties_FK_entity_id' => $ar->entity_properties_FK_entity_id,
                    'entity_properties_type' => $ar->entity_properties_type,
                    'entity_properties_target_FK_entity_id' => $ar->entity_properties_target_FK_entity_id,
                    'entity_properties_label_key' => $ar->entity_properties_label_key,
                    'entity_properties_required' => $ar->entity_properties_required,
                    'entity_properties_show_label_in_frontend' => $ar->entity_properties_show_label_in_frontend,
                    'entity_properties_relation_show' => $ar->entity_properties_relation_show,
                    'entity_properties_reference_relation_show' => $ar->entity_properties_reference_relation_show,
                    'entity_properties_dublic_core' => $ar->entity_properties_dublic_core,
                    'entity_properties_row_index' => $ar->entity_properties_row_index,
                    'entity_properties_params' => $ar->entity_properties_params
                );

                $entityTypes[$ar->entity_id]['types'][$ar->entity_properties_type][] = $attributeId;

                // trova l'attributo immagine
                if (!isset($entityTypes[$ar->entity_id]['imageAttribute']) && $ar->entity_properties_type == 'attribute.image' || $ar->entity_properties_type == 'attribute.image') {
                    $entityTypes[$ar->entity_id]['imageAttribute'] = $attributeId;
                }

                // trova l'attributo descrizione
                if (!isset($descriptionAttributeDc[$ar->entity_id])) {
                    // cerca l'attributo con dcField uguale a DC.Description
                    if ($ar->entity_properties_dublic_core == 'DC.Description') {
                        $entityTypes[$ar->entity_id]['descriptionAttribute'] = $attributeId;
                        $descriptionAttributeDc[$ar->entity_id] = true;
                    } else {
                        // verifica che l'attributo non sia una relazione e che sia testuale
                        if (!isset($entityTypes[$ar->entity_id]['descriptionAttribute']) && $ar->entity_properties_dublic_core == '' && preg_match('/text$/', $ar->entity_properties_type)) {
                            $entityTypes[$ar->entity_id]['descriptionAttribute'] = $attributeId;
                        }
                    }
                }
            }
        }

        foreach($entityTypes as $entityId => $entity) {
            $it = org_glizy_objectFactory::createModelIterator('movio.modules.ontologybuilder.models.EntityProperties');
            $it->load('entityRelationsFromId', array('entityId' => $entityId));
            foreach($it as $ar) {
                $entityTypes[$entityId]['reference_relations'][] = array (
                    'entity_properties_id' => $ar->entity_properties_id,
                    'entity_properties_FK_entity_id' => $ar->entity_properties_FK_entity_id,
                    'entity_properties_type' => $ar->entity_properties_type,
                    'entity_properties_target_FK_entity_id' => $ar->entity_properties_target_FK_entity_id,
                    'entity_properties_label_key' => $ar->entity_properties_label_key,
                    'entity_properties_required' => $ar->entity_properties_required,
                    'entity_properties_show_label_in_frontend' => $ar->entity_properties_show_label_in_frontend,
                    'entity_properties_relation_show' => $ar->entity_properties_relation_show,
                    'entity_properties_reference_relation_show' => $ar->entity_properties_reference_relation_show,
                    'entity_properties_dublic_core' => $ar->entity_properties_dublic_core,
                    'entity_properties_row_index' => $ar->entity_properties_row_index,
                    'entity_properties_params' => $ar->entity_properties_params
                );
            }
        }

        $this->entityTypes["entityTypes"] = $entityTypes;
    }

    public function getEntityTypeId($entityType)
    {
        return str_replace('entity', '', $entityType);
    }

    public function getAttributeIdById($id)
    {
        return 'attribute'.$id;
    }

    public function getAttributeIdByAr($ar)
    {
        return 'attribute'.$ar->entity_properties_id;
    }

    public function getAttributeIdByProperties($properties)
    {
        return 'attribute'.$properties['entity_properties_id'];
    }

    public function getAttributeByType($entityTypeId, $attributeType)
    {
        $result = $this->entityTypes["entityTypes"][$entityTypeId]['types'][$attributeType];

        if (count($result) == 1) {
            return $result[0];
        } else {
            return $result;
        }
    }

    public function getRelations()
    {
        return $this->entityTypes["relations"];
    }

    public function getRelation($relation)
    {
        return $this->entityTypes["relations"][$relation];
    }

    public function getEntityTypeName($entityTypeId)
    {
        return __Tp($this->entityTypes["entityTypes"][$entityTypeId]['name']);
    }

    public function getEntityTypeAttribute($entityTypeId, $attribute)
    {
        return $this->entityTypes["entityTypes"][$entityTypeId][$attribute];
    }

    public function getEntityTypeProperties($entityTypeId)
    {
        return $this->entityTypes["entityTypes"][$entityTypeId]['properties'];
    }

    public function getEntityTypeAttributeProperties($entityTypeId, $attributeId)
    {
        return $this->entityTypes["entityTypes"][$entityTypeId]['properties'][$attributeId];
    }

    public function getEntityTypeReferenceRelations($entityTypeId)
    {
        return $this->entityTypes["entityTypes"][$entityTypeId]['reference_relations'];
    }

    public function getDescriptionAttribute($entityTypeId)
    {
        return $this->entityTypes["entityTypes"][$entityTypeId]['descriptionAttribute'];
    }

    public function getImageAttribute($entityTypeId)
    {
        return $this->entityTypes["entityTypes"][$entityTypeId]['imageAttribute'];
    }

    function onRegister()
    {
    }
}