<?php
class org_glizycms_contents_controllers_pageEdit_ajax_SaveProperties extends org_glizy_mvc_core_CommandAjax
{
    public function execute($data)
    {
// TODO: controllo acl
        $data = json_decode($data);

        $menu = org_glizy_ObjectFactory::createModel('org.glizycms.core.models.Menu');
        $menu->load($data->menu_id);
        $menu->menu_url = $data->menu_url;
        $menu->menu_isLocked = $data->menu_isLocked;
        $menu->menu_hasComment = $data->menu_hasComment;
        $menu->menu_printPdf = $data->menu_printPdf;
        $menu->menu_pageType = $data->menu_pageType;
        $menu->menu_cssClass = $data->menu_cssClass;
        if (@$data->menu_creationDate) $menu->menu_creationDate = $data->menu_creationDate;
        $menu->save();


        $menu = org_glizy_ObjectFactory::createModel('org.glizycms.core.models.MenuDetail');
        $menu->find(array('menudetail_FK_menu_id' => $data->menu_id, 'menudetail_FK_language_id' => org_glizy_ObjectValues::get('org.glizy', 'editingLanguageId')));
        $menu->menudetail_title = $data->menudetail_title;
        $menu->menudetail_titleLink = $data->menudetail_titleLink;
        $menu->menudetail_linkDescription = $data->menudetail_linkDescription;
        $menu->menudetail_keywords = $data->menudetail_keywords;
        $menu->menudetail_description = $data->menudetail_description;
        $menu->menudetail_subject = $data->menudetail_subject;
        $menu->menudetail_creator = $data->menudetail_creator;
        $menu->menudetail_publisher = $data->menudetail_publisher;
        $menu->menudetail_contributor = $data->menudetail_contributor;
        $menu->menudetail_type = $data->menudetail_type;
        $menu->menudetail_identifier = $data->menudetail_identifier;
        $menu->menudetail_source = $data->menudetail_source;
        $menu->menudetail_relation = $data->menudetail_relation;
        $menu->menudetail_coverage = $data->menudetail_coverage;
        $menu->save();

        $menuProxy = org_glizy_ObjectFactory::createObject('org.glizycms.contents.models.proxy.MenuProxy');
        $menuProxy->invalidateSitemapCache();

        return true;
    }
}