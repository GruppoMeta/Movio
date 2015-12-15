<?php
class org_glizycms_core_application_renderer_CellUser extends org_glizy_components_render_RenderCell
{
    function renderCell( $key, $value, $item, $columnName )
    {
        $ar = org_glizy_ObjectFactory::createModel('org.glizy.models.User');
        $ar->load($value);
        return $ar->user_firstName.' '.$ar->user_lastName;
    }
}
