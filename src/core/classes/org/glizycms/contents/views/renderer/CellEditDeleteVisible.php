<?php
class org_glizycms_contents_views_renderer_CellEditDeleteVisible extends org_glizycms_contents_views_renderer_AbstractCellEdit
{
    function renderCell($key, $value, $row)
    {
        $this->loadAcl($key);
        $output = $this->renderEditButton($key, $row).
                    $this->renderDeleteButton($key, $row).
                    $this->renderVisibilityButton($key, $row);
        return $output;
    }
}

