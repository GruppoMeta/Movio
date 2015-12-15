<?php
class movio_modules_ontologybuilder_views_components_EntityList extends org_glizy_components_RecordSetList
{
    function init()
    {
        $this->defineAttribute('visualization', false, 'list', COMPONENT_TYPE_STRING);
        $this->defineAttribute('entityTypeId', false, 0, COMPONENT_TYPE_INTEGER);
        parent::init();

        $this->setAttribute('skin', 'Entity_list.html');
    }


    function process()
    {
        $entityTypeId = $this->getAttribute('entityTypeId');
        if ($entityTypeId) {
            $entityTypeService = $this->_application->retrieveProxy('movio.modules.ontologybuilder.service.EntityTypeService');
            $skinAttributes = unserialize($entityTypeService->getEntityTypeAttribute($entityTypeId, 'entity_skin_attributes'));
            if ($skinAttributes) {
                parent::process();
                $this->_content->params = new StdClass;
                $this->_content->params->visualization = $this->getAttribute('visualization');
                $this->_content->params->attributes = $skinAttributes;
            }
        }
    }
}
