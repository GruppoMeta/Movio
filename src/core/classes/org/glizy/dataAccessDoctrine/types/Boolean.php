<?php

use Doctrine\DBAL\Platforms\AbstractPlatform;

class org_glizy_dataAccessDoctrine_types_Boolean extends \Doctrine\DBAL\Types\BooleanType
{
    public function convertToDatabaseValue($value, AbstractPlatform $platform = null)
    {
        $value = $value==='false' ? 0 :
                    ($value==='true' ? 1 : $value);
        return parent::convertToDatabaseValue($value, $platform);
    }
}
