<?php
class movio_modules_ontologybuilder_views_components_Entity extends org_glizy_components_ComponentContainer
{
    function process()
    {
        $entityProxy = org_glizy_objectFactory::createObject('movio.modules.ontologybuilder.models.proxy.EntityProxy');
        $this->_content = $entityProxy->loadContentFrontend((int)$this->getId());

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
                    $groupBox->addChild($c);
                    break;

                case 'attribute.descriptivetext':
                    $c = &org_glizy_ObjectFactory::createComponent('org.glizy.components.LongText', $this->_application, $this, 'glz:LongText', $attribute, $attribute);
                    $c->setAttribute('adm:htmlEditor', true);
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
                    $c = &org_glizy_ObjectFactory::createComponent('org.glizy.components.Image', $this->_application, $this, 'glz:Image', $attribute, $attribute);
                    $c->setAttribute('width', __Config::get('IMG_WIDTH'));
                    $c->setAttribute('height', __Config::get('IMG_HEIGHT'));
                    $c->setAttribute('zoom', true);
                    $c->setAttribute('superZoom', true);
                    $groupBox->addChild($c);
                    break;

                case 'attribute.media':
                    $c = &org_glizy_ObjectFactory::createComponent('org.glizy.components.Media', $this->_application, $this, 'glz:Media', $attribute, $attribute);
                    $groupBox->addChild($c);
                    break;

                case 'attribute.imagelist':
                    if ($this->_content[$attribute]) {
                        $c = &org_glizy_ObjectFactory::createComponent('movio.modules.ontologybuilder.views.components.Repeater', $this->_application, $this, 'cmp:Repeater', $attribute, $attribute);

                        $subchild = &org_glizy_ObjectFactory::createComponent('org.glizy.components.Image', $this->_application, $c, 'glz:Image', 'attaches', 'attaches');
                        $subchild->setAttribute('width', __Config::get('THUMB_WIDTH'));
                        $subchild->setAttribute('height', __Config::get('THUMB_HEIGHT'));
                        $subchild->setAttribute('zoom', true);
                        $c->addChild($subchild);

                        $groupBox->addChild($c);
                    }
                    break;

                case 'attribute.medialist':
                    if ($this->_content[$attribute]) {
                        $c = &org_glizy_ObjectFactory::createComponent('movio.modules.ontologybuilder.views.components.Repeater', $this->_application, $this, 'cmp:Repeater', $attribute, $attribute);

                        $subchild = &org_glizy_ObjectFactory::createComponent('org.glizy.components.Media', $this->_application, $c, 'glz:Media', 'attaches', 'attaches');
                        $c->addChild($subchild);

                        $groupBox->addChild($c);
                    }
                    break;

                case 'attribute.photogallery':
                    $c = &org_glizy_ObjectFactory::createComponent('movio.views.components.PhotogalleryCategory', $this->_application, $this, 'm:PhotogalleryCategory', $attribute, $attribute);
                    $c->setAttribute('label', $localeService->getTranslation($language, $ar->entity_properties_label_key));
                    $c->setAttribute('editableRegion', 'photogallery');
                    $groupBoxRel->addChild($c);
                    break;

                case 'attribute.europeanaRelatedContents':
                    $c = &org_glizy_ObjectFactory::createComponent('movio.modules.europeana.views.components.RelatedContents', $this->_application, $this, 'evc:RelatedContents', $attribute, $attribute);
                    $c->setAttribute('skin', 'EuropeanaRelatedContents.html');
                    $c->setAttribute('label', $localeService->getTranslation($language, $ar->entity_properties_label_key));
                    $c->setAttribute('editableRegion', 'europeanaRelatedContents');
                    $groupBoxRel->addChild($c);
                    break;

                case 'attribute.thesaurus':
                    $c = &org_glizy_ObjectFactory::createComponent('movio.modules.ontologybuilder.views.components.Thesaurus', $this->_application, $this, 'cmp:Thesaurus', $attribute, $attribute);
                    $groupBox->addChild($c);
                    break;

                case 'attribute.module':
                    $c = &org_glizy_ObjectFactory::createComponent('movio.modules.ontologybuilder.views.components.RepeaterForModule', $this->_application, $this, 'cmp:Repeater', $attribute, $attribute);
                    $c->setAttribute('label', $localeService->getTranslation($language, $ar->entity_properties_label_key));
                    $c->setAttribute('skin', 'Entity_moduleImageLink.html');
                    $c->setAttribute('editableRegion', 'photogallery');

                    $module = &org_glizy_ObjectFactory::createComponent('movio.modules.ontologybuilder.views.components.Module', $this->_application, $c, 'cmp:Module', 'attaches', 'attaches');
                    $c->addChild($module);

                    $groupBoxRel->addChild($c);

                default:
                    // se l'attributo è una relazione
                    if (!is_null($ar->entity_properties_target_FK_entity_id) && $ar->entity_properties_relation_show != 3 && $this->_content[$attribute]['content']) {
                        $c = &org_glizy_ObjectFactory::createComponent('movio.modules.ontologybuilder.views.components.Repeater', $this->_application, $this, 'cmp:Repeater', $attribute, $attribute);

                        $relation = &org_glizy_ObjectFactory::createComponent('movio.modules.ontologybuilder.views.components.Relation', $this->_application, $c, 'cmp:Relation', 'attaches', 'attaches');
                        $c->setAttribute('skin', $skinsName[$ar->entity_properties_relation_show]);
                        $c->setAttribute('editableRegion', 'relations');
                        $c->addChild($relation);

                        $groupBoxRel->addChild($c);
                    }
            }
        }

        foreach ((array)$this->_content['__reference_relations'] as $entityTypeName => $data) {
            if ($data['show'] != 3) {
                $c = &org_glizy_ObjectFactory::createComponent('movio.modules.ontologybuilder.views.components.Repeater', $this->_application, $this, 'cmp:Repeater', $entityTypeName, $entityTypeName);

                $relation = &org_glizy_ObjectFactory::createComponent('movio.modules.ontologybuilder.views.components.Relation', $this->_application, $c, 'cmp:Relation', 'attaches'.$entityTypeName, 'attaches'.$entityTypeName);
                $c->setAttribute('skin', $skinsName[$data['show']]);
                $c->setAttribute('editableRegion', 'referenceRelations');
                $c->addChild($relation);

                $groupBoxRel->addChild($c);
            }
        }

        if ($entityTypeService->getEntityTypeAttribute($entityTypeId, 'entity_show_relations_graph')) {
            $c = &org_glizy_ObjectFactory::createComponent('movio.modules.ontologybuilder.views.components.DocumentGraph', $this->_application, $this, 'cmp:DocumentGraph', 'document_graph', 'document_graph');
            $c->setAttribute('editableRegion', 'relationsGraph');
            $groupBoxRel->addChild($c);
        }
    }

    function loadContent($id, $bindTo = '')
    {
        // se $id è l'id di una reference relation
        if (preg_match('/^__entity\d+$/', $id, $m)) {
            return $this->_content['__reference_relations'][$m[0]];
        }
        else {
            return $this->_content[$id];
        }
    }

    function addOutputCode($output, $editableRegion='', $atEnd=false)
    {
        if ($editableRegion=='relationsGraph' ||
                $editableRegion=='relations' ||
                $editableRegion=='referenceRelations' ||
                $editableRegion=='europeanaRelatedContents' ||
                $editableRegion=='photogallery'  ) {
            if (!property_exists($this->_content['relations'], $editableRegion)) {
                $this->_content['relations']->{$editableRegion} = '';
            }
            $this->_content['relations']->{$editableRegion} .= $output;
            return;
        }
        parent::addOutputCode($output, $editableRegion, $atEnd);
    }
}
