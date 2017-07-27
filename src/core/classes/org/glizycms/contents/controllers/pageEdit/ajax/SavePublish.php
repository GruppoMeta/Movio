<?php
class org_glizycms_contents_controllers_pageEdit_ajax_SavePublish extends org_glizycms_contents_controllers_pageEdit_ajax_Save
{

    public function execute($data, $status, $reloadUrl)
    {
        $this->checkPermissionForBackend();
        $this->directOutput = true;

        $result = $this->save($data, false, true);
        if ($result===true) {
            $url = __Link::addParams(array('status' => org_glizycms_contents_views_components_PageEdit::STATUS_PUBLISHED), false, $reloadUrl);
            return array(
                'evt' => 'glizycms.pageEdit',
                'message' => array('menuId' => $this->menuId.'&status='.org_glizycms_contents_views_components_PageEdit::STATUS_PUBLISHED)
                );
        }

        return $result;
    }
}
