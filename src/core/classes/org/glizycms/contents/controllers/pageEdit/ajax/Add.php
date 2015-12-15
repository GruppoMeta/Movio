<?php
class org_glizycms_contents_controllers_pageEdit_ajax_Add extends org_glizy_mvc_core_CommandAjax
{
    public function execute($data)
    {
        $this->directOutput = true;
        $data = json_decode($data);
        if ($data) {
// TODO: controllo acl
            $pageTitle = $data->title;
            $pageParent = $data->pageParent;
            $pageType = $data->pageType;
            if ($pageTitle && $pageParent && $pageType) {
                $menuProxy = org_glizy_ObjectFactory::createObject('org.glizycms.contents.models.proxy.MenuProxy');
                $pageId = $menuProxy->addMenu(  $pageTitle,
                                                $pageParent,
                                                $pageType,
                                                strtolower(__Request::get('action'))=='addblock' ? org_glizycms_core_models_enum_MenuEnum::BLOCK : org_glizycms_core_models_enum_MenuEnum::PAGE
                                                );
                return array(
                            'evt' => 'glizycms.pageAdded',
                            'message' => array('menuId' => $pageId, 'parentId' => $pageParent)
                        );
            }
        }
        return false;
    }
}