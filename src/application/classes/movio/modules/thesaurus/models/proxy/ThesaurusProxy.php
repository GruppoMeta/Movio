<?php
class movio_modules_thesaurus_models_proxy_ThesaurusProxy extends GlizyObject
{
    public function addDictionary($name, $type)
    {
        $ar = org_glizy_ObjectFactory::createModel('movio.modules.thesaurus.models.Dictionary');
        $ar->title = $name;
        $ar->type = $type;
        try {
            $ar->save(null, false, 'PUBLISHED');
        } catch (movio_modules_thesaurus_Exception $e) {
            throw movio_modules_thesaurus_Exception::dictionaryCreationError($name);
        }

        return $ar;
    }

    public function getTypeByDictionaryId($dictionaryId)
    {
        $ar = org_glizy_objectFactory::createModel('movio.modules.thesaurus.models.Dictionary');
        $ar->load($dictionaryId);
        return $ar->type ?: movio_modules_thesaurus_models_TermTypeEnum::GENERIC;
    }

    public function getAllDictionaries()
    {
        $results = array();
        $it = org_glizy_ObjectFactory::createModelIterator('movio.modules.thesaurus.models.Dictionary')
            ->orderBy('title');
        foreach ($it as $ar) {
            $results[] = movio_modules_thesaurus_models_DictionaryVO::createFromAr($ar);
        }

        return $results;
    }

    public function getTermVO($type)
    {
        return movio_modules_thesaurus_models_TermFactory::createTermFromType($type);
    }

    public function saveTerm($termVO)
    {
        if (!in_array($termVO->type, movio_modules_thesaurus_models_TermTypeEnum::getTypes())) {
            throw movio_modules_thesaurus_Exception::termWrongType($termVO->type);
        }
        $ar = org_glizy_ObjectFactory::createModel('movio.modules.thesaurus.models.Term');

        if ($termVO->getId()) {
            $ar->load($termVO->getId());
        }

        foreach ($termVO as $k => $v) {
            $ar->$k = $v;
        }

        try {
            $ar->save(null, false, 'PUBLISHED');
        } catch (movio_modules_thesaurus_Exception $e) {
            throw movio_modules_thesaurus_Exception::termCreationError($termVO->term);
        }

        return $ar;
    }

    public function loadTerm($termId)
    {
        return movio_modules_thesaurus_models_TermFactory::createTermFromId($termId);
    }

    public function getFirstLevelChildrens($dictionaryId, $parentId=0)
    {
        $results = array();
        //if ($parentId) {
            $it = org_glizy_ObjectFactory::createModelIterator('movio.modules.thesaurus.models.Term')
            ->where('dictionaryId', $dictionaryId)
            ->where('parentId', $parentId);
        /*} else {
            $it = org_glizy_ObjectFactory::createModelIterator('movio.modules.thesaurus.models.Term')
            ->where('dictionaryId', $dictionaryId);
        }*/
        $it->orderBy('term');
        foreach ($it as $ar) {
            $results[] = movio_modules_thesaurus_models_TermFactory::createTermFromAr($ar);
        }
        return $results;
    }

    public function getDictionaryById($dictionaryId)
    {
        $ar = org_glizy_objectFactory::createModel('movio.modules.thesaurus.models.Dictionary');
        $ar->load($dictionaryId);
        return movio_modules_thesaurus_models_DictionaryVO::createFromAr($ar);
    }

    public function getAllTerms($dictionaryId)
    {
        $results = array();
        $it = org_glizy_ObjectFactory::createModelIterator('movio.modules.thesaurus.models.Term')
            ->where('dictionaryId', $dictionaryId)
            ->orderBy('term');

        foreach ($it as $ar) {
            $results[] = movio_modules_thesaurus_models_TermFactory::createTermFromAr($ar);
        }

        return $results;
    }

    public function deleteTerm($termId)
    {
        $ar = org_glizy_ObjectFactory::createModel('movio.modules.thesaurus.models.Term');
        $ar->load($termId);

        $childrens = $this->getFirstLevelChildrens($ar->dictionaryId, $termId);

        foreach ($childrens as $child) {
            $this->deleteTerm($child->getId());
        }

        $ar->delete();
    }

    public function deleteDictionary($dictionaryId)
    {
        $ar = org_glizy_ObjectFactory::createModel('movio.modules.thesaurus.models.Dictionary');
        $ar->delete($dictionaryId);

        $it = org_glizy_ObjectFactory::createModelIterator('movio.modules.thesaurus.models.Term')
            ->where('dictionaryId', $dictionaryId);

        foreach ($it as $ar) {
            $ar->delete();
        }
    }

    public function searchTerm($term)
    {
        return $this->findTerm(null, null, null, $term);
    }

    // metodo per selectfrom
    public function findTerm($fieldName, $model, $query, $term, $proxyParams = null)
    {
        $it = org_glizy_ObjectFactory::createModelIterator('movio.modules.thesaurus.models.Term')
            ->where('term',  '%'.$term.'%', 'ILIKE');

        if ($proxyParams) {
            foreach ($proxyParams as $k => $v) {
                $it->where($k, $v);
            }
        }

        $result = array();

        foreach ($it as $ar) {
            // TODO ottimizzare in una query sola
            $ar1 = org_glizy_ObjectFactory::createModel('movio.modules.thesaurus.models.Term');
            $ar1->load($ar->parentId);

            $ar2 = org_glizy_ObjectFactory::createModel('movio.modules.thesaurus.models.Term');
            $ar2->load($ar1->parentId);

            $ar3 = org_glizy_ObjectFactory::createModel('movio.modules.thesaurus.models.Term');
            $ar3->load($ar2->parentId);

            $path = array($ar3->term, $ar2->term, $ar1->term);

            $result[] = array(
                'id' => $ar->getId(),
                'text' => $ar->term,
                'path' => ltrim(implode('/', $path), '/')
            );
        }

        return $result;
    }

    public function moveTerm($termId, $newParentId)
    {
        $ar = org_glizy_ObjectFactory::createModel('movio.modules.thesaurus.models.Term');
        $ar->load($termId);
        $ar->parentId = $newParentId;
        $ar->save();
    }

    /**
     * @param int $dictionaryId
     * @return array
     */
    public function getDocumentsWithDictionaryOrTerm($dictionaryId, $termId=null)
    {
        $application = org_glizy_ObjectValues::get('org.glizy', 'application');
        $entityTypeService = $application->retrieveProxy('movio.modules.ontologybuilder.service.EntityTypeService');
        $entityResolver = org_glizy_objectFactory::createObject('movio.modules.ontologybuilder.EntityResolver');
        $menuMap = array();

        $it = org_glizy_ObjectFactory::createModelIterator('movio.modules.ontologybuilder.models.EntityDocument')
            ->load('documentWithDictionaryOrTerm', array('dictionaryId' => $dictionaryId, 'termId' => $termId));

        $result = array();
        foreach ($it as $ar) {
            $entityTypeId = $entityTypeService->getEntityTypeId($ar->getType());
            $arMenu = $this->menuFromEnityId($entityTypeId, $menuMap, $entityResolver);
            if (!$arMenu) {
                continue;
            }

            $descriptionAttribute = $entityTypeService->getDescriptionAttribute($entityTypeId);
            $document_id = $ar->getId();
            $item = array(
                'id' => $document_id,
                'title' => $ar->title,
                'description' => glz_strtrim(($descriptionAttribute && $ar->keyInDataExists($descriptionAttribute)) ? $ar->$descriptionAttribute : '', 300),
                'url' => __Routing::makeUrl('showEntityDetail', array('pageId' => $arMenu->id, 'entityTypeId' => $entityTypeId,'document_id' => $document_id))
            );

            if (!isset($result[$ar->termId])) {
                $termJson = json_decode($ar->term);
                $term = movio_modules_thesaurus_models_TermFactory::createTermFromType($termJson->type);
                $term->setFromObject($termJson);
                $result[$ar->termId] = array(   'term' => $term,
                                                'taggedDocuments' => array());
            }
            
            $result[$ar->termId]['taggedDocuments'][] = $item;
        }

        return $result;
    }

     /**
     * @param int $termId
     * @return array
     */
    public function getDocumentsWithTerm($termId)
    {
        $term = $this->loadTerm($termId);
        $result = $this->getDocumentsWithDictionaryOrTerm($term->dictionaryId, $termId);
        return isset($result[$termId]) ? $result[$termId]['taggedDocuments'] : array();
    }
    
    /**
     * @param int $entityTypeId
     * @param array $menuMap
     * @param movio_modules_ontologybuilder_EntityResolver $entityResolver
     * @return object
     */
    private function menuFromEnityId($entityTypeId, &$menuMap, $entityResolver)
    {
        if (!isset($menuMap[$entityTypeId])) {
            $arMenu = $entityResolver->getMenuVisibleEntity($entityTypeId);
            $menuMap[$entityTypeId] = $arMenu;
        }

        return $menuMap[$entityTypeId];;
    }

}
