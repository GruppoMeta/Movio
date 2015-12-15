<?php

use Doctrine\DBAL\Platforms\AbstractPlatform;

class org_glizy_dataAccessDoctrine_types_DateTime extends \Doctrine\DBAL\Types\DateTimeType
{
    public function convertToDatabaseValue($value, AbstractPlatform $platform = null)
    {
        return glz_localeDate2ISO($value);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform = null)
    {
        return is_string($value) ? glz_defaultDate2locale(__T('GLZ_DATETIME_FORMAT'), $value) : $value;
    }
}
