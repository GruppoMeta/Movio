<?php

use Doctrine\DBAL\Platforms\AbstractPlatform;

class org_glizy_dataAccessDoctrine_types_Date extends \Doctrine\DBAL\Types\DateType
{
    public function convertToDatabaseValue($value, AbstractPlatform $platform = null)
    {
        return glz_localeDate2ISO($value);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform = null)
    {
        return is_string($value) ? glz_defaultDate2locale(__T('GLZ_DATE_FORMAT'), $value) : $value;
    }
}
