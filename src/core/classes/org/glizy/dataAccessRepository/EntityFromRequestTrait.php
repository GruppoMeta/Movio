<?php

trait org_glizy_dataAccessRepository_EntityFromRequestTrait
{
    use org_glizy_dataAccessRepository_EntityBuilderTrait;

    public static function fromRequest($request)
    {
        return self::createEntity(get_class(), (array)$request);
    }
}