<?php
trait org_glizy_dataAccessRepository_EntityTrait
{
    public function isNew()
    {
        $id = $this->getId();
        return is_null($id) || !$id;
    }
}