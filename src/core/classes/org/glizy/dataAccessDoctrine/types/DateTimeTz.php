<?php

use Doctrine\DBAL\Platforms\AbstractPlatform;

class org_glizy_dataAccessDoctrine_types_DateTimeTz extends \Doctrine\DBAL\Types\DateTimeTzType
{
    public function convertToDatabaseValue($value, AbstractPlatform $platform = null)
    {
        return $value;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value === null || $value instanceof org_glizy_types_DateTimeTz) {
            return $value;
        }

        $val = org_glizy_types_DateTimeTz::createFromFormat($platform->getDateTimeTzFormatString(), $value);
        if ( ! $val) {
            throw ConversionException::conversionFailedFormat($value, $this->getName(), $platform->getDateTimeTzFormatString());
        }
        return $val;
    }
}
