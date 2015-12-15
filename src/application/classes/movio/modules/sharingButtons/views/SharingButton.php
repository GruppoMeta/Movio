<?php
class movio_modules_sharingButtons_views_SharingButton
{
    CONST REGISTRY_SHAREBUTTONS = '/shareButtons';

    public static function getSharingButtonList()
    {
        return unserialize(org_glizy_Registry::get(__Config::get('BASE_REGISTRY_PATH').self::REGISTRY_SHAREBUTTONS, ''));
    }

    public static function setSharingButtonList($shareButtons)
    {
        org_glizy_Registry::set(__Config::get('BASE_REGISTRY_PATH').self::REGISTRY_SHAREBUTTONS, serialize($shareButtons));
    }

}