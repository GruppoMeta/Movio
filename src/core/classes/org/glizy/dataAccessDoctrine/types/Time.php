<?php

use Doctrine\DBAL\Platforms\AbstractPlatform;

class org_glizy_dataAccessDoctrine_types_Time extends \Doctrine\DBAL\Types\TimeType
{
    public function convertToDatabaseValue($value, AbstractPlatform $platform = null)
    {
        return $value;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value === null || $value instanceof org_glizy_types_Time) {
            return $value;
        }

        $val = org_glizy_types_Time::createFromFormat('!'.$platform->getTimeFormatString(), $value);
        if ( ! $val) {
            throw ConversionException::conversionFailedFormat($value, $this->getName(), $platform->getTimeFormatString());
        }
        return $val;
    }
}
