<?php

use Doctrine\DBAL\Platforms\AbstractPlatform;

class org_glizy_dataAccessDoctrine_types_ArrayID extends org_glizy_dataAccessDoctrine_types_Array
{
    public function convertToDatabaseValue($value, \Doctrine\DBAL\Platforms\AbstractPlatform $platform = null)
    {
        return $value;
    }
}
