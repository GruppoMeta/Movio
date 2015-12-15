<?php
class movio_modules_thesaurus_views_renderer_CellType extends org_glizycms_contents_views_renderer_AbstractCellEdit
{

    function renderCell($key, $value, $row)
    {
        return __T( ucfirst($value) );
    }
}
