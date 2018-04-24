<?php
class movio_modules_ontologybuilder_views_renderer_ActionEditDelete extends org_glizy_components_render_RenderCellRecordSetList
{
    function renderCell(&$ar, $params)
	{
	    $localeService = $this->application->retrieveProxy('movio.modules.ontologybuilder.service.LocaleService');
        $language = $this->application->getEditingLanguage();
        $ar->entity_name = $localeService->getTranslation($language, $ar->entity_name);
	   
        // TODO controllo acl
        $ar->__url__ =  __Link::makeLinkWithIcon('actionsMVC', 'icon-pencil icon-white', array('action' => 'edit', 'id' => $ar->getId(), 'title' => __T('GLZ_RECORD_EDIT')));
        $ar->__urlDelete__ =  __Link::makeLinkWithIcon('actionsMVC', 'icon-remove icon-white', array('action' => 'delete', 'id' => $ar->getId(), 'title' => __T('GLZ_RECORD_EDIT')), __T('GLZ_RECORD_MSG_DELETE'));
	}
}