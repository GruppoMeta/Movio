<?php

class movio_search_Content extends org_glizy_plugins_PluginClient
{
    function run(&$parent, $params)
    {
        $application = org_glizy_ObjectValues::get('org.glizy', 'application');
        $languageId = $application->getLanguageId();
        $language = $application->getLanguage();

        $it = org_glizy_objectFactory::createModelIterator('movio.search.models.Content');
        $it->load('getVisibleEntities', array(':words' => $params, ':language' => $languageId));

        foreach ($it as $ar) {
            //$ar->dump();

            $result = $parent->getResultStructure();
			$result['title'] = $ar->title;
            $result['description'] = $ar->description;

            if ($ar->keyInDataExists('url') && $ar->url) {
                $url = org_glizy_helpers_Html::renderTag('a', array('href' => $language.'/'.$ar->url), true, $ar->title);
            } else {
                $url = __Link::makeLink('link', array('pageId' => $ar->pageId, 'title' => $ar->title));
            }

            $result['__url__'] 	= $url;

			$parent->addResult($result);
        }
	}
}