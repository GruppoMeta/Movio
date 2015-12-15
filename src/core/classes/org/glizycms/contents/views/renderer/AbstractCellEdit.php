<?php
// TODO: spostare in un path più corretto non dentro contents
abstract class org_glizycms_contents_views_renderer_AbstractCellEdit extends org_glizy_components_render_RenderCell
{
    protected $canView = true;
    protected $canEdit = true;
    protected $canDelete = true;

    protected function loadAcl($key)
    {
        // TODO: posstare questa parte di codice in un classe comune
        // e gestire in modo simile quando sono attivi i ruoli e quando no
        $pageId = $this->application->getPageId();
        if (__Config::get('ACL_ROLES')) {
            if (!$this->user->acl($pageId, 'all')) {
                $this->canView = $this->user->acl($pageId, 'visible');
                $this->canEdit = $this->user->acl($pageId, 'edit');
                $this->canDelete = $this->user->acl($pageId, 'delete');

                if ($this->canView) {
                    $ar = org_glizy_objectFactory::createModel('org.glizycms.contents.models.DocumentACL');
                    $ar->load($key);

                    if ($ar->__aclEdit) {
                        $roles = explode(',', $ar->__aclEdit);
                        $this->canEdit = $this->canDelete = $this->user->isInRoles($roles);
                    }
                }
            }
        } else {
            $this->canView = $this->user->acl($pageId, 'visible');
            $this->canEdit = $this->user->acl($pageId, 'edit');
            $this->canDelete = $this->user->acl($pageId, 'delete');
        }
    }

    protected function renderEditButton($key, $row, $enabled = true)
    {
        $output = '';
        if ($this->canView && $this->canEdit) {
            $output = __Link::makeLinkWithIcon(
                'actionsMVC',
                __Config::get('glizy.datagrid.action.editCssClass').($enabled ? '' : ' disabled'),
                array(
                    'title' => __T('GLZ_RECORD_EDIT'),
                    'id' => $key,
                    'action' => 'edit'
                )
            );
        }

        return $output;
    }

    protected function renderEditDraftButton($key, $row, $enabled = true)
    {
        $output = '';
        if ($this->canView && $this->canEdit) {
            $output = __Link::makeLinkWithIcon(
                'actionsMVC',
                __Config::get('glizy.datagrid.action.editDraftCssClass').($enabled ? '' : ' disabled'),
                array(
                    'title' => __T('GLZ_RECORD_EDIT_DRAFT'),
                    'id' => $key,
                    'action' => 'editDraft'
                )
            );
        }

        return $output;
    }

    protected function renderDeleteButton($key, $row)
	{
        $output = '';
        if ($this->canView && $this->canDelete) {
            $output .= __Link::makeLinkWithIcon( 'actionsMVCDelete',
                                                            __Config::get('glizy.datagrid.action.deleteCssClass'),
                                                            array(
                                                                'title' => __T('GLZ_RECORD_DELETE'),
                                                                'id' => $key,
                                                                'model' => $row->getClassName(false),
                                                                'action' => 'delete'  ),
                                                            __T('GLZ_RECORD_MSG_DELETE') );
        }

		return $output;
	}

    protected function renderVisibilityButton($key, $row)
    {
        $output = '';
        if ($this->canView && $this->canEdit) {
            $output .= __Link::makeLinkWithIcon( 'actionsMVCToggleVisibility',
                                                           __Config::get($row->isVisible() ? 'glizy.datagrid.action.showCssClass' : 'glizy.datagrid.action.hideCssClass'),
                                                           array(
                                                                'title' => $row->isVisible() ? __T('Hide') : __T('Show'),
                                                                'id' => $key,
                                                                'model' => $row->getClassName(false),
                                                                'action' => 'togglevisibility' ));
        }

        return $output;
    }

    protected function renderCheckBox($key, $row)
	{
        $output = '';
        if ($this->canView && $this->canDelete) {
            $output .= '<input name="check[]" data-id="'.$row->getId().'" type="checkbox">';
        }

		return $output;
	}
}

