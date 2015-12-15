<?php
class org_glizycms_template_models_TemplateVO extends GlizyObject
{
    public $name;
    public $path;
    public $preview;

    function __construct($name, $path, $preview) {
        $this->name = str_replace('-', ' ', $name);
        $this->path = $path;
        $this->preview = $preview;
    }
}
