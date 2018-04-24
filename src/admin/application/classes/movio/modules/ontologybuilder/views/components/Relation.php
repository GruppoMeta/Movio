<?php
class movio_modules_ontologybuilder_views_components_Relation extends org_glizy_components_ComponentContainer
{
    function process()
    {
        $this->_content = $this->_parent->loadContent($this->getId());

        $this->createChildComponents();
        $this->initChilds();
        $this->processChilds();
    }

    function createChildComponents()
    {
        $entityTypeId = $this->_content['entityTypeId'];

        $c = &org_glizy_ObjectFactory::createComponent('org.glizy.components.Text', $this->_application, $this, 'glz:Text', 'title', 'title');
        $this->addChild($c);

        $this->_content['url'] = __Routing::makeUrl('showEntityDetail', array('entityTypeId' => $entityTypeId, 'document_id' => $this->_content['document_id']));

        $c = &org_glizy_ObjectFactory::createComponent('org.glizy.components.Text', $this->_application, $this, 'glz:Text', 'url', 'url');
        $this->addChild($c);

        $entityTypeService = $this->_application->retrieveProxy('movio.modules.ontologybuilder.service.EntityTypeService');

        $it = org_glizy_objectFactory::createModelIterator('movio.modules.ontologybuilder.models.EntityProperties');
        $it->load('entityPropertiesFromId', array('entityId' => $entityTypeId));

        foreach ($it as $ar) {
            $attribute = $entityTypeService->getAttributeIdByAr($ar);

            switch ($ar->entity_properties_type) {
                case 'attribute.image':
                    if (is_null($this->_content['__image']) && $this->_content[$attribute]){
                        $c = &org_glizy_ObjectFactory::createComponent('org.glizy.components.Image', $this->_application, $this, 'glz:Image', '__image', '__image');
                        $c->setAttribute('width', __Config::get('THUMB_WIDTH'));
                        $c->setAttribute('height', __Config::get('THUMB_HEIGHT'));
                        $c->setAttribute('crop', true);
                        $this->addChild($c);
                        $this->_content['__image'] = $this->_content[$attribute];
                    }
                    break;

            }

            if (is_null($this->_content['__description']) && $ar->entity_properties_dublic_core == 'DC.Description' && $this->_content[$attribute]) {
                $c = &org_glizy_ObjectFactory::createComponent('org.glizy.components.LongText', $this->_application, $this, 'glz:LongText', '__description', '__description');
                $c->setAttribute('adm:htmlEditor', true);
                $this->addChild($c);
                $this->_content['__description'] = glz_strtrim(strip_tags($this->_content[$attribute]));
            }
        }

        // controlla se esiste una lista media/immagini e ne prende la prima
        if (is_null($this->_content['__image'])) {
            foreach ($it as $ar) {
                $attribute = $entityTypeService->getAttributeIdByAr($ar);

                switch ($ar->entity_properties_type) {
                    case 'attribute.imagelist':
                        if (is_null($this->_content['__image']) && $this->_content[$attribute]){
                            $objectVars = get_object_vars($this->_content[$attribute]);
                            $content = array_shift($objectVars);

                            $c = &org_glizy_ObjectFactory::createComponent('org.glizy.components.Image', $this->_application, $this, 'glz:Image', '__image', '__image');
                            $c->setAttribute('width', __Config::get('THUMB_SMALL_WIDTH'));
                            $c->setAttribute('height', __Config::get('THUMB_SMALL_HEIGHT'));
                            $c->setAttribute('crop', true);
                            $this->addChild($c);
                            $this->_content['__image'] = $content[0];
                        }
                        break;
                }
            }
        }

        // inserisce un'immagine di default
        if (is_null($this->_content['__image'])) {
            $c = &org_glizy_ObjectFactory::createComponent('movio.modules.ontologybuilder.views.components.NoImage', $this->_application, $this, 'glz:Image', '__image', '__image');
            $c->setAttribute('width', __Config::get('THUMB_SMALL_WIDTH'));
            $c->setAttribute('height', __Config::get('THUMB_SMALL_HEIGHT'));
            $this->addChild($c);
        }

        if (is_null($this->_content['__description'])) {
            foreach ($it as $ar) {
                $attribute = $entityTypeService->getAttributeIdByAr($ar);

                if (is_null($ar->entity_properties_target_FK_entity_id) && preg_match('/text$/', $ar->entity_properties_type) && $this->_content[$attribute] && !is_array($this->_content[$attribute]) && !is_object($this->_content[$attribute])) {
                    $c = &org_glizy_ObjectFactory::createComponent('org.glizy.components.LongText', $this->_application, $this, 'glz:LongText', '__description', '__description');
                    $c->setAttribute('adm:htmlEditor', true);
                    $this->addChild($c);
                    $this->_content['__description'] = glz_strtrim(strip_tags($this->_content[$attribute]));
                }
            }
        }
    }

    function getContent()
    {
        $r = $this->getChildContent();
        return $r;
    }

    function loadContent($id, $bindTo = '')
    {
        return $this->_content[$id];
    }
}
?>