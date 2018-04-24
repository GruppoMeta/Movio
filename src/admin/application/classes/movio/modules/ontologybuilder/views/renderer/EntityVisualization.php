<?php
class movio_modules_ontologybuilder_views_renderer_EntityVisualization extends org_glizy_components_render_RenderCellRecordSetList
{
    function renderCell( &$ar, $params )
    {
        $ar->__image = '';

        foreach ($ar->getValuesAsArray() as $value) {
            if (is_string($value) && preg_match('/getImage.php\?id=(\d+)/', $value, $m)) {
                $id = $m[1];
                $ar->__image = org_glizy_helpers_Media::getResizedImageById($id, true,
                                __Config::get('movio.thumb.entityList.width'),
                                __Config::get('movio.thumb.entityList.height'),
                                __Config::get('movio.thumb.entityList.crop')
                                );
                break;
            }
        }

        if ($ar->__image == '') {
            $ar->__image ='<img src="'.__Config::get('movio.noImage.src').'" width="'.__Config::get('movio.thumb.entityList.width').'" height="'.__Config::get('movio.thumb.entityList.height').'"/>';
        }

        // TODO: verificare perché viene letto dalla request
        if (!__Request::get('visualization')) {
            $ar->visualization = 'list';
        } else {
            $ar->visualization = __Request::get('visualization');
        }
    }
}