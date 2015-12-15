<?php
class movio_modules_thesaurus_models_TermVO
{
    public $__id;
    public $term;
    public $type;
    public $parentId;
    public $dictionaryId;

    public function getId()
    {
        return $this->__id;
    }

    public static function createFromAr($ar)
    {
        $vo = new self;
        $vo->__id = $ar->getId();
        $vo->term = $ar->term;
        $vo->type = $ar->type;
        $vo->parentId = $ar->parentId;
        $vo->dictionaryId = $ar->dictionaryId;
        return $vo;
    }

    public function setFromObject($data)
    {
        foreach ($data as $k => $v) {
            // remove the system values
            if (strpos($k, 'pageEdit_command') === 0) continue;
            $this->$k = $v;
        }
    }

    public function setDictionaryId($dictionaryId)
    {
        $this->dictionaryId = $dictionaryId;
    }
}