<?php
glz_import('org.glizy.helpers.Link');

class movio_modules_ontologybuilder_views_renderer_ActionEditDeleteEntity extends GlizyObject
{
    function renderCell( $key, $value, $row )
	{
        $entityId = $key;
        $entityTypeId = str_replace('entity', '', $row->getType());

        $output = __Link::makeLinkWithIcon('actionEntities', 'btn-icon icon-pencil', array('title' => __T('GLZ_RECORD_EDIT'), 'entityTypeId' => $entityTypeId, 'entityId' => $entityId, 'action' => 'edit' ) );
        $output .= __Link::makeLinkWithIcon('actionEntities', 'icon-trash btn-icon' ,array( 'title' => __T('GLZ_RECORD_DELETE'), 'entityTypeId' => $entityTypeId, 'entityId' => $entityId, 'action' => 'delete' ), __T( 'GLZ_RECORD_MSG_DELETE' ) );
        $output .= __Link::makeLinkWithIcon('actionEntities', $row->isVisible() ? 'icon-eye-open btn-icon' : 'icon-eye-close btn-icon', array( 'title' => $row->isVisible() ? __T('Hide') : __T('Show'), 'entityTypeId' => $entityTypeId, 'entityId' => $entityId, 'action' => 'togglevisibility'  ) );

		return $output;
	}
}