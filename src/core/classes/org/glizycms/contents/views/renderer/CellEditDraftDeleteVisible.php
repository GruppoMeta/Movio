<?php
class org_glizycms_contents_views_renderer_CellEditDraftDeleteVisible extends org_glizycms_contents_views_renderer_AbstractCellEdit
{
    function renderCell($key, $value, $row)
    {
        $this->loadAcl($key);

        $output = $this->renderEditButton($key, $row, $row->hasPublishedVersion()).
                  $this->renderEditDraftButton($key, $row, $row->hasDraftVersion()).
                  $this->renderDeleteButton($key, $row).
                  $this->renderVisibilityButton($key, $row);

        return $output;
    }
}


