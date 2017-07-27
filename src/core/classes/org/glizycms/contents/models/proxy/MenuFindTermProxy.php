<?php
class org_glizycms_contents_models_proxy_MenuFindTermProxy
{
    function findTerm($fieldName, $model, $query, $term, $proxyParams)
    {
        $oldMultisite =  __Config::get('MULTISITE_ENABLED');
        __Config::set('MULTISITE_ENABLED', false);

        if ($proxyParams && property_exists($proxyParams, 'filterType')) {
            $filterType = $proxyParams->filterType;
        }
        $selfId = __Request::get('menu_id');
        $languageId = org_glizy_ObjectValues::get('org.glizy', 'editingLanguageId');
        $it = org_glizy_ObjectFactory::createModelIterator('org.glizycms.core.models.Menu');

            $it->load('autocompletePagePicker', array('search' => '%'.$term.'%', 'languageId' => $languageId, 'menuId' => '', 'pageType' => $filterType));


        $result = array();
        foreach($it as $ar) {
            if ($selfId==$ar->menu_id) {
                continue;
            }

            $result[] = array(
                'id' => $ar->menu_id,
                'text' => $ar->menudetail_title
            );
        }

        __Config::set('MULTISITE_ENABLED', $oldMultisite);
        return $result;
    }
}
