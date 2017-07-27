<?php
class org_glizycms_contents_controllers_pageEdit_ajax_SaveDraft extends org_glizycms_contents_controllers_pageEdit_ajax_Save
{

    public function execute($data, $status, $menuId)
    {
        $this->checkPermissionForBackend();
        $this->directOutput = true;

        $reload = $status!=org_glizycms_contents_views_components_PageEdit::STATUS_DRAFT;
        $result = $this->save($data, true);
        if ($result===true && $reload) {
            return array(
                'evt' => 'glizycms.pageEdit',
                'message' => array('menuId' => $this->menuId.'&status='.org_glizycms_contents_views_components_PageEdit::STATUS_DRAFT)
                );
        }

        return $result;
    }
}



