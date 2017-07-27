<?php
class org_glizycms_mediaArchive_models_vo_MediaInfoVO
{
    private $ar;

    function __construct($ar)
    {
        $this->ar = $ar;
    }

    public function __get($name)
    {
        if ($name=='title') {
            $name = 'menudetail_title';
        } else if ($name=='description') {
            $name = 'menudetail_description';
        }

       return $this->ar->{$name};
    }
}