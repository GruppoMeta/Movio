<?php
class movio_modules_ontologybuilder_models_vo_SkinAttributesVO
{
    public $description = '';
    public $figure = array();
    public $properties = array();
    public $body = array();

    public function setDescriptionAttribute($name)
    {
        $this->description = $name;
    }

    public function setDetailAttributes($properties, $figure, $body)
    {
        $this->properties = $properties;
        $this->figure = $figure;
        $this->body = $body;
    }
}