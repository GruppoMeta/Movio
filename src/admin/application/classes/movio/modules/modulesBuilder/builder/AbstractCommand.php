<?php
class movio_modules_modulesBuilder_builder_AbstractCommand
{
	var $_application;
	var $parent;

	function __construct( $parent )
	{
		$this->parent = $parent;
		$this->_application = org_glizy_ObjectValues::get('org.glizy', 'application');
	}

	function getError()
	{
		return '';
	}
}
