<?php
class org_glizycms_contents_views_renderer_CellEdit extends org_glizycms_contents_views_renderer_AbstractCellEdit
{
    function renderCell($key, $value, $row)
    {
        $this->loadAcl($key);
        $output = $this->renderEditButton($key, $row);
        return $output;
    }
}


