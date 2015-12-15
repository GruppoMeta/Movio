<?php
class movio_modules_thesaurus_models_DictionaryVO
{
    public $title;
    public $type;
    public $id;

    public static function createFromAr($ar)
    {
        $vo = new self;
        $vo->id = $ar->getId();
        $vo->title = $ar->title;
        $vo->type = $ar->type;
        return $vo;
    }

    public function getId()
    {
        return $this->id;
    }
}