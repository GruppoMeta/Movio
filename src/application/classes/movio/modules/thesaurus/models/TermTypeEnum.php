<?php
class movio_modules_thesaurus_models_TermTypeEnum
{
    const GENERIC = 'generic';
    const GEOGRAPHICAL = 'geographical';
    const CHRONOLOGIC = 'chronologic';

    static public function getTypes()
    {
        return array(
                    self::GENERIC,
                    self::GEOGRAPHICAL,
                    self::CHRONOLOGIC
            );
    }
}