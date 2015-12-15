<?php
class movio_modules_ontologybuilder_Paths
{
    const BASE_PATH = 'classes/userModules/movio/ontologybuilder/';

    public static function getLocalePath($languageId)
    {
        return __Paths::getRealPath('APPLICATION_TO_ADMIN').self::BASE_PATH.'locale/'.$languageId.'.php';
    }
}