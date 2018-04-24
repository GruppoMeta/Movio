<?php
class movio_modules_ontologybuilder_views_components_Module extends org_glizy_components_ComponentContainer
{
    protected $data;

    function process()
    {
        $this->data = $this->_parent->loadContent($this->getId());

        $this->createChildComponents();
        $this->initChilds();
        $this->processChilds();
    }

    function createChildComponents()
    {
        $entityTypeService = $this->_application->retrieveProxy('movio.modules.ontologybuilder.service.EntityTypeService');
        $properties = $entityTypeService->getEntityTypeAttributeProperties(__Request::get('entityTypeId'), $this->_parent->getId());
        $moduleId = $properties['entity_properties_params'];

        $this->_content['title'] = $this->data->text;
        $c = &org_glizy_ObjectFactory::createComponent('org.glizy.components.Text', $this->_application, $this, 'glz:Text', 'title', 'title');
        $this->addChild($c);

        $this->_content['url'] = __Routing::makeUrl($moduleId, array('document_id' => $this->data->id, 'title' => $this->data->text));
        $c = &org_glizy_ObjectFactory::createComponent('org.glizy.components.Text', $this->_application, $this, 'glz:Text', 'url', 'url');
        $this->addChild($c);

        $module = org_glizy_Modules::getModule($moduleId);
        $ar = org_glizy_ObjectFactory::createModel($module->classPath.'.models.Model');
        $ar->load($this->data->id);

        if ($ar->fieldExists('image')) {
            $this->_content['__image'] = $ar->image;
            $c = &org_glizy_ObjectFactory::createComponent('org.glizy.components.Image', $this->_application, $this, 'glz:Image', '__image', '__image');
            $c->setAttribute('width', __Config::get('THUMB_WIDTH'));
            $c->setAttribute('height', __Config::get('THUMB_HEIGHT'));
            $c->setAttribute('crop', true);
            $this->addChild($c);

        } else {
            $c = &org_glizy_ObjectFactory::createComponent('movio.modules.ontologybuilder.views.components.NoImage', $this->_application, $this, 'glz:Image', '__image', '__image');
            $c->setAttribute('width', __Config::get('THUMB_SMALL_WIDTH'));
            $c->setAttribute('height', __Config::get('THUMB_SMALL_HEIGHT'));
            $this->addChild($c);
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