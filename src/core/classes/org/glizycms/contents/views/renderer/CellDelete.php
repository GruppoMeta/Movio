<?php
class org_glizycms_contents_views_renderer_CellDelete extends org_glizycms_contents_views_renderer_AbstractCellEdit
{
    function renderCell($key, $value, $row)
    {
        $this->loadAcl($key);
        $output = $this->renderDeleteButton($key, $row);
        return $output;
    }
}


