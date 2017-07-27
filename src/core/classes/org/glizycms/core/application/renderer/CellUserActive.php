<?php
// TODO
// fare una superclasse processCell
// e una renderCell

// TODO rinominare in CellIsChecked
class org_glizycms_core_application_renderer_CellUserActive extends GlizyObject
{
	function renderCell($key, $value)
	{
		if ($value=='1' || $value===true) $value = '<span class="'.__Config::get('glizy.datagrid.checkbox.on').'"></span>';
		else $value = '<span class="'.__Config::get('glizy.datagrid.checkbox.off').'"></span>';
		return $value;
	}
}
