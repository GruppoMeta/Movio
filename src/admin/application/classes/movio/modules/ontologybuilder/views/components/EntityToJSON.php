<?php
class movio_modules_ontologybuilder_views_components_EntityToJSON extends org_glizy_components_ComponentContainer
{
    protected $json;
    protected $medias;
    protected $graph;

    function process()
    {
        $this->json = array(
            'title' => '',
            'subtitle' => '',
            'content' => '',
            'relations' => array(),
            'reference_relations' => array(),
            'images' => array(),
            'imageList' => array(),
            'medias' => array(),
            'mediaList' => array(),
            'photogallery' => array()
        );

        $this->medias = array();

        $entityProxy = org_glizy_objectFactory::createObject('movio.modules.ontologybuilder.models.proxy.EntityProxy');
        $this->_content = $entityProxy->loadContentFrontend($this->getId());

        $this->createChildComponents();
        $this->initChilds();
        $this->processChilds();

        // simulate a new page in site structure
        // for update the navigation menu, breadcrumbs and page title
        $currentMenu    = &$this->_application->getCurrentMenu();
        $siteMap        = &$this->_application->getSiteMap();
        $menu = org_glizy_application_SiteMap::getEmptyMenu();
        $menu['title']      = $this->_content['title'];
        $menu['id']         = $currentMenu->id+100000;
        $menu['pageType']   = $currentMenu->pageType;
        // $menu['isVisible']   = false;
        $menu['url']        = __Request::get('__url__');
        $siteMap->addChildMenu($currentMenu, $menu);
        //$this->_application->setPageId($menu['id']);

        $evt = array('type' => GLZ_EVT_SITEMAP_UPDATE, 'data' => $menu['id'] );
        $this->dispatchEvent($evt);
    }

    function createChildComponents()
    {
        $entityTypeId = $this->_content['entityTypeId'];
        $entityTypeService = $this->_application->retrieveProxy('movio.modules.ontologybuilder.service.EntityTypeService');
        $skinAttributes = unserialize($entityTypeService->getEntityTypeAttribute($entityTypeId, 'entity_skin_attributes'));
        if (!$skinAttributes) {
            return;
        }

        $language = $this->_application->getLanguage();
        $localeService = $this->_application->retrieveProxy('movio.modules.ontologybuilder.service.LocaleService');

        foreach ($skinAttributes->properties as $i => $property) {
            $skinAttributes->properties[$i]['label'] = $localeService->getTranslation($language, $property['label']);
        }

        foreach ($skinAttributes->body as $i => $body) {
            $skinAttributes->body[$i]['label'] = $localeService->getTranslation($language, $body['label']);
        }

        $groupBoxRel = &org_glizy_ObjectFactory::createComponent('org.glizy.components.Groupbox', $this->_application, $this, 'glz:Groupbox', 'relations');
        $this->addChild($groupBoxRel);

        $skinsName = array('Entity_relationImage.html', 'Entity_relationLink.html', 'Entity_relationImageLink.html');
        $groupBox = &org_glizy_ObjectFactory::createComponent('org.glizy.components.Groupbox', $this->_application, $this, 'glz:Groupbox', 'detail');
        $groupBox->setAttribute('skin', 'Entity_entry.html');
        $this->addChild($groupBox);
        $this->_content['attributes'] = $skinAttributes;
        $this->_content['relations'] = new StdClass;

        $c = &org_glizy_ObjectFactory::createComponent('org.glizy.components.Hidden', $this->_application, $this, 'glz:Hidden', 'attributes', 'attributes');
        $groupBox->addChild($c);
        $c = &org_glizy_ObjectFactory::createComponent('org.glizy.components.Hidden', $this->_application, $this, 'glz:Hidden', 'relations', 'relations');
        $groupBox->addChild($c);

        if (isset($this->_content['subtitle']) && $this->_content['subtitle']) {
            $c = &org_glizy_ObjectFactory::createComponent('org.glizy.components.PageTitle', $this->_application, $this, 'glz:Text', 'subtitle', 'subtitle');
            $c->setAttributes(array('tag' => 'h2', 'editableRegion' => 'pageTitle', 'value' => $this->_content['subtitle']));
            $this->addChild($c);
        }

        // TODO usare EntityTypeService per ottenere le proprietà in cache
        $it = org_glizy_objectFactory::createModelIterator('movio.modules.ontologybuilder.models.EntityProperties');
        $it->load('entityPropertiesFromId', array('entityId' => $entityTypeId));

        $jsonRelations = array();

        foreach ($it as $ar) {
            $attribute = $entityTypeService->getAttributeIdByAr($ar);

            switch ($ar->entity_properties_type) {
                case 'attribute.text':
                case 'attribute.int':
                case 'attribute.date':
                case 'attribute.openlist':
                    $c = &org_glizy_ObjectFactory::createComponent('org.glizy.components.Text', $this->_application, $this, 'glz:Text', $attribute, $attribute);
                    $groupBox->addChild($c);
                    break;

                case 'attribute.longtext':
                    $c = &org_glizy_ObjectFactory::createComponent('org.glizy.components.LongText', $this->_application, $this, 'glz:LongText', $attribute, $attribute);
                    $c->setAttribute('forceP', true);
                    $c->setAttribute('parseInternalLinks', false);
                    $groupBox->addChild($c);
                    break;

                case 'attribute.descriptivetext':
                    $c = &org_glizy_ObjectFactory::createComponent('org.glizy.components.LongText', $this->_application, $this, 'glz:LongText', $attribute, $attribute);
                    $c->setAttribute('adm:htmlEditor', true);
                    $c->setAttribute('parseInternalLinks', false);
                    $groupBox->addChild($c);
                    break;

                case 'attribute.externallink':
                case 'attribute.internallink':
                    $c = &org_glizy_ObjectFactory::createComponent('org.glizy.components.LinkTo', $this->_application, $this, 'glz:LinkTo', $attribute, $attribute);
                    $groupBox->addChild($c);
                    break;

                case 'attribute.externalimage':
                    $c = &org_glizy_ObjectFactory::createComponent('org.glizy.components.ImageExternal', $this->_application, $this, 'glz:ImageExternal', $attribute, $attribute);
                    $groupBox->addChild($c);
                    break;

                case 'attribute.image':
                    $this->addMedia('images', $this->_content[$attribute]);
                    break;

                case 'attribute.media':
                    $this->addMedia('medias', $this->_content[$attribute]);
                    break;

                case 'attribute.imagelist':
                    if ($this->_content[$attribute]) {
                        foreach ($this->_content[$attribute]->attaches as $attach) {
                            $this->addMedia('imageList', $attach);
                        }
                    }
                    break;

                case 'attribute.medialist':
                    if ($this->_content[$attribute]) {
                        foreach ($this->_content[$attribute]->attaches as $attach) {
                            $this->addMedia('mediaList', $attach);
                        }
                    }
                    break;

                case 'attribute.photogallery':
                    if ($this->_content[$attribute.'-images']) {
                        foreach ($this->_content[$attribute.'-images'] as $category) {
                            $it = org_glizy_ObjectFactory::createModelIterator('org.glizycms.models.Media');
                            $it->where('media_category', '%'.$category.'%', 'LIKE')
                               ->orderBy('media_title');

                            foreach($it as $ar) {
                                $media = array(
                                    'id' => $ar->media_id,
                                    'title' => $ar->media_title,
                                    'fileName' => $ar->media_fileName
                                );
                                $this->addMedia('photogallery', json_encode($media));
                            }
                        }
                    }
                    break;

                default:
                    // se l'attributo è una relazione
                    if (!is_null($ar->entity_properties_target_FK_entity_id) && $ar->entity_properties_relation_show != 3 && $this->_content[$attribute]['content']) {
                        $relations = ($this->loadContent($attribute));
                        $entityTypeName = $entityTypeService->getEntityTypeName($ar->entity_properties_target_FK_entity_id);

                        $jsonRelations[$entityTypeName] = array();

                        foreach ($relations['content'] as $relation) {
                            $jsonRelation = array();
                            $jsonRelation['title'] = $relation['title'];
                            $image = '';

                            foreach ($relation as $k => $v) {
                                if (is_string($v) && preg_match('/{"id":\d+,/', $v, $m)) {
                                    $image = $v;
                                    break;
                                }
                            }

                            $image = $this->addMedia('', $image);
                            $jsonRelation['image'] = $image;
                            $jsonRelation['entityTypeId'] = $relation['entityTypeId'];
                            $jsonRelation['document_id'] = $relation['document_id'];

                            $jsonRelations[$entityTypeName][] = $jsonRelation;
                        }
                    }
            }
        }

        if ($jsonRelations) {
            $this->json['relations'][] = $jsonRelations;
        }

        $jsonRelations = array();

        foreach ((array)$this->_content['__reference_relations'] as $entityTypeName => $data) {
            if ($data['show'] != 3) {
                $entityTypeName = $data['relation'];

                $jsonRelations[$entityTypeName] = array();

                foreach ($data['content'] as $relation) {
                    $jsonRelation = array();
                    $jsonRelation['title'] = $relation['title'];
                    $image = '';

                    foreach ($relation as $k => $v) {
                        if (is_string($v) && preg_match('/{"id":\d+,/', $v, $m)) {
                            $image = $v;
                            break;
                        }
                    }

                    $image = $this->addMedia('', $image);
                    $jsonRelation['image'] = $image;
                    $jsonRelation['entityTypeId'] = $relation['entityTypeId'];
                    $jsonRelation['document_id'] = $relation['document_id'];

                    $jsonRelations[$entityTypeName][] = $jsonRelation;
                }

            }
        }

        if ($jsonRelations) {
            $this->json['reference_relations'][] = $jsonRelations;
        }

        if ($entityTypeService->getEntityTypeAttribute($entityTypeId, 'entity_show_relations_graph')) {
            $c = &org_glizy_ObjectFactory::createComponent('movio.modules.publishApp.views.components.DocumentGraph', $this->_application, $this, 'cmp:DocumentGraph', 'document_graph', 'document_graph');
            $c->setAttribute('entityTypeId', $entityTypeId);
            $c->setAttribute('documentId', $this->getId());
            $c->setAttribute('addGraphJsLibs.js', false);
            $c->setAttribute('editableRegion', 'graph');
            $this->addChild($c);
        }

        foreach (array('images', 'imageList', 'medias', 'mediaList', 'photogallery') as $k) {
            if ($this->json[$k]) {
                $this->json[$k] = array_values($this->json[$k]);
            }
        }

        $this->addChild($groupBox);
    }

    function addMedia($key, $media) {
        if ($media != '')  {
            $result = new StdClass();
            $media = json_decode($media);
            $m = org_glizycms_mediaArchive_MediaManager::getMediaById($media->id);
            if ($m == null) {
                return null;
            }
            $result->type = $m->type;
            $result->title = $media->title;
            $media->type = $m->type;
            if ($result->type == 'VIDEO') {
                $result->url = GLZ_HOST.'/'.org_glizy_helpers_Media::getFileUrlById($media->id, true);
            } else {
                $result->fileName = $media->fileName;
                $this->medias[$media->id] = $media->fileName;
            }

            if ($key) {
                $this->json[$key][$media->id] = $result;
            }
        }
        return $result;
    }

    public function getMedias()
    {
        return $this->medias;
    }

    function addOutputCode($output, $editableRegion='', $atEnd=null)
    {
        if ($editableRegion == 'graph') {
            $this->graph .= $output;
        } else {
            $this->json['content'] .= $output;
        }
    }

    function loadContent($id)
    {
        // se $id è l'id di una reference relation
        if (preg_match('/^__entity\d+$/', $id, $m)) {
            return $this->_content['__reference_relations'][$m[0]];
        }
        else {
            return $this->_content[$id];
        }
    }

    public function getJson()
    {
        return $this->json;
    }

    public function getGraph()
    {
        return $this->graph;
    }
}
?>