<?php
class movio_modules_thesaurus_models_GenericTermVO extends movio_modules_thesaurus_models_TermVO
{
    public $url;
    public $note;

    public static function createFromAr($ar)
    {
        $vo = new self;
        $vo->__id = $ar->getId();
        $vo->term = $ar->term;
        $vo->type = movio_modules_thesaurus_models_TermTypeEnum::GENERIC;
        $vo->parentId = $ar->parentId;
        $vo->dictionaryId = $ar->dictionaryId;
        $vo->url = $ar->url;
        $vo->note = $ar->note;
        return $vo;
    }
}