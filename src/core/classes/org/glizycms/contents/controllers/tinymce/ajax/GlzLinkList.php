<?php
class org_glizycms_contents_controllers_tinymce_ajax_GlzLinkList extends org_glizy_mvc_core_CommandAjax
{
    function execute()
    {
        $this->directOutput = true;
        $links = array('internal' => array());

        $menuProxy = org_glizy_ObjectFactory::createObject('org.glizycms.contents.models.proxy.MenuProxy');
        $siteMap = $menuProxy->getSiteMap();
        $siteMap->getSiteArray();
        $siteMapIterator = &org_glizy_ObjectFactory::createObject('org.glizy.application.SiteMapIterator', $siteMap);
        while (!$siteMapIterator->EOF)
        {
            $n = $siteMapIterator->getNodeArray();
            if ($n['type'] != org_glizycms_core_models_enum_MenuEnum::BLOCK ) {
                $links['internal'][] = array(   'name' => str_repeat('.  ', $n["depth"]-1).strip_tags($n['title']),
                                                'link' => 'internal:'.$n['id']);
            }
            $siteMapIterator->moveNext();
        }
        return $links;
    }
}