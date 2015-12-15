<?php

class movio_search_Entity extends org_glizy_plugins_PluginClient
{
    function run(&$parent, $params)
    {
        $application = org_glizy_ObjectValues::get('org.glizy', 'application');
        $languageId = $application->getLanguageId();
        $language = $application->getLanguage();

        $it = org_glizy_objectFactory::createModelIterator('movio.search.models.Entity');
        $it->load('getVisibleEntities', array(':words' => $params, ':language' => $languageId));

        foreach ($it as $ar) {
            //$ar->dump();

            $application = org_glizy_ObjectValues::get('org.glizy', 'application');
            $entityTypeService = $application->retrieveProxy('movio.modules.ontologybuilder.service.EntityTypeService');
            $descriptionAttribute = $entityTypeService->getDescriptionAttribute($ar->entityTypeId);

            $result = $parent->getResultStructure();
            $result['title'] = $ar->title;
            $result['description'] = ($descriptionAttribute && $ar->keyInDataExists($descriptionAttribute)) ? $ar->$descriptionAttribute : '';

            if ($ar->keyInDataExists('url') && $ar->url) {
                $url = org_glizy_helpers_Html::renderTag('a', array('href' => $language.'/'.$ar->url), true, $ar->title);
            } else {
                $url = __Link::makeLink('showEntityDetail', $ar->getValuesAsArray());
            }

            $result['__url__']     = $url;

            $parent->addResult($result);
        }
    }
}