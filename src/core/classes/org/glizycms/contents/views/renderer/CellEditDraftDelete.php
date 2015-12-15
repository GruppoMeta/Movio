<?php
class org_glizycms_contents_views_renderer_CellEditDraftDelete extends org_glizycms_contents_views_renderer_AbstractCellEdit
{
    function renderCell($key, $value, $row)
    {
        $this->loadAcl($key);

        $output = $this->renderEditButton($key, $row, $row->hasPublishedVersion()).
                  $this->renderEditDraftButton($key, $row, $row->hasDraftVersion()).
                  $this->renderDeleteButton($key, $row);

        return $output;
    }
}


