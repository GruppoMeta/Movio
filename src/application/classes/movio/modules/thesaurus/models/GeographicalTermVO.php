<?php
class movio_modules_thesaurus_models_GeographicalTermVO extends movio_modules_thesaurus_models_TermVO
{
    public $geo;

    public static function createFromAr($ar)
    {
        $vo = new self;
        $vo->__id = $ar->getId();
        $vo->term = $ar->term;
        $vo->type = movio_modules_thesaurus_models_TermTypeEnum::GEOGRAPHICAL;
        $vo->parentId = $ar->parentId;
        $vo->dictionaryId = $ar->dictionaryId;
        $vo->geo = $ar->geo;
        return $vo;
    }
}