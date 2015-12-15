<?php
class movio_modules_thesaurus_models_ChronologicTermVO extends movio_modules_thesaurus_models_TermVO
{
    public $dateFrom;
    public $dateTo;

    public static function createFromAr($ar)
    {
        $vo = new self;
        $vo->__id = $ar->getId();
        $vo->term = $ar->term;
        $vo->type = movio_modules_thesaurus_models_TermTypeEnum::CHRONOLOGIC;
        $vo->parentId = $ar->parentId;
        $vo->dictionaryId = $ar->dictionaryId;
        $vo->dateFrom = $ar->dateFrom;
        $vo->dateTo = $ar->dateTo;
        return $vo;
    }
}