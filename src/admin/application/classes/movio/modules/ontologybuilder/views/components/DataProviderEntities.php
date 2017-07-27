<?php
class movio_modules_ontologybuilder_views_components_DataProviderEntities extends org_glizy_components_Component
{
    public function getItems($name, $bindToField=NULL)
    {
        $localeService = $this->_application->retrieveProxy('movio.modules.ontologybuilder.service.LocaleService');
        $language = $this->_application->getEditingLanguage();
        $it = __ObjectFactory::createModelIterator('movio.modules.ontologybuilder.models.Entity')
                ->load('all');

        $result = array();
        foreach ($it as $ar) {
            $result[] = array('key' => $ar->entity_id, 'value' => $localeService->getTranslation($language, $ar->entity_name));
        }

        return $result;
    }
}