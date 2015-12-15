<?php
class movio_modules_ontologybuilder_views_components_NoImage extends org_glizy_components_Component
{
    function init()
    {
		// define the custom attributes
		$this->defineAttribute('height',		false, 	NULL,	COMPONENT_TYPE_INTEGER);
		$this->defineAttribute('width',			false, 	NULL,	COMPONENT_TYPE_INTEGER);

		// call the superclass for validate the attributes
		parent::init();
	}
    
    function getContent()
    {
        $width = $this->getAttribute('width') ? $this->getAttribute('width') : __Config::get('THUMB_WIDTH');
        $height = $this->getAttribute('height') ? $this->getAttribute('height') :__Config::get('THUMB_HEIGHT');
        return array('__html__' => '<img src="'.__Config::get('movio.noImage.src').'" width="'.$width.'" height="'.$height.'"/>'); 
    }
}
?>