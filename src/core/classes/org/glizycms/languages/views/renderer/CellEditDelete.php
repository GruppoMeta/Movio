<?php
class org_glizycms_languages_views_renderer_CellEditDelete extends org_glizy_components_render_RenderCell
{
    function renderCell( $key, $value, $item, $columnName )
	{
        $pageId = $this->application->getPageId();
        if ($this->user->acl($pageId, 'all') or $this->user->acl($pageId, 'edit')) {
            $output = org_glizy_Assets::makeLinkWithIcon(  'actionsMVC',
                                                        'icon-pencil btn-icon',
                                                        array(
                                                            'title' => __T('GLZ_RECORD_EDIT'),
                                                            'id' => $key,
                                                            'action' => 'edit'));
        }
        	
        if (!$item->language_isDefault) {
            $output .= org_glizy_Assets::makeLinkWithIcon(   'actionsMVC',
                                                            'icon-trash btn-icon',
                                                            array(
                                                                'title' => __T('GLZ_RECORD_DELETE'),
                                                                'id' => $key,
                                                                'action' => 'delete'),
                                                            __T( 'conferma cancellazione record'));
        }

		return $output;
	}
}

