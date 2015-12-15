<?php
class movio_modules_thesaurus_models_TermFactory
{
    public static function createTermFromId($termId)
    {
        $ar = org_glizy_ObjectFactory::createModel('movio.modules.thesaurus.models.Term');
        $ar->load($termId);
        return self::createTermFromAr($ar);
    }

    public static function createTermFromAr($ar)
    {
        switch ($ar->type) {
            case movio_modules_thesaurus_models_TermTypeEnum::GENERIC:
                return movio_modules_thesaurus_models_GenericTermVO::createFromAr($ar);

            case movio_modules_thesaurus_models_TermTypeEnum::GEOGRAPHICAL:
                return movio_modules_thesaurus_models_GeographicalTermVO::createFromAr($ar);

            case movio_modules_thesaurus_models_TermTypeEnum::CHRONOLOGIC:
                return movio_modules_thesaurus_models_ChronologicTermVO::createFromAr($ar);
        }
    }

    public static function createTermFromType($type)
    {
        switch ($type) {
            case movio_modules_thesaurus_models_TermTypeEnum::GENERIC:
                $termVO = org_glizy_ObjectFactory::createObject('movio.modules.thesaurus.models.GenericTermVO');
                break;

            case movio_modules_thesaurus_models_TermTypeEnum::GEOGRAPHICAL:
                $termVO = org_glizy_ObjectFactory::createObject('movio.modules.thesaurus.models.GeographicalTermVO');
                break;

            case movio_modules_thesaurus_models_TermTypeEnum::CHRONOLOGIC:
                $termVO = org_glizy_ObjectFactory::createObject('movio.modules.thesaurus.models.ChronologicTermVO');
                break;
        }

        $termVO->type = $type;
        return $termVO;
    }
}