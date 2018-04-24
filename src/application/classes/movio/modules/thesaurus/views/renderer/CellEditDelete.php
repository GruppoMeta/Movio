<?php
class movio_modules_thesaurus_views_renderer_CellEditDelete extends org_glizycms_contents_views_renderer_AbstractCellEdit
{

    function renderCell($key, $value, $row, $columnName)
    {
        $this->loadAcl($value);
        $output = $this->renderEditButton($value, $row).
                    $this->renderDeleteButton($value, $row);
        return $output;
    }


    protected function renderEditButton($key, $row, $enabled = true)
    {
        $output = '';

        if ($this->canView && $this->canEdit) {
            $output = __Link::makeLinkWithIcon( 'actionsMVC',
                                                            'icon-pencil btn-icon',
                                                            array(
                                                                'title' => __T('GLZ_RECORD_EDIT'),
                                                                'action' => 'editDictionary'),
                                                                NULL,
                                                                array('dictionaryId' => $key));
        }

        return $output;
    }

    protected function renderDeleteButton($key, $row)
    {

        $output = '';
        if ($this->canView && $this->canDelete) {
            $output .= __Link::makeLinkWithIcon( 'actionsMVC',
                                                            'icon-trash btn-icon',
                                                            array(
                                                                'title' => __T('GLZ_RECORD_DELETE'),
                                                                'action' => 'deleteDictionary'  ),
                                                            __T('GLZ_RECORD_MSG_DELETE'),
                                                             array('dictionaryId' => $key));
        }

        return $output;
    }

}


