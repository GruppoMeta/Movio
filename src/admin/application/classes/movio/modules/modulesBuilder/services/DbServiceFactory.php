<?php

class movio_modules_modulesBuilder_services_DbServiceFactory extends GlizyObject
{
    public function createDbService($dbType)
    {
        if ($dbType == 'mysql') {
            return org_glizy_objectFactory::createObject('movio.modules.modulesBuilder.services.MysqlService', 11);
        } else if ($dbType == 'pgsql') {
            return org_glizy_objectFactory::createObject('movio.modules.modulesBuilder.services.PgsqlService', 12);
        } else {
            $this->logAndMessage('Il dbms selezionato non è supportato', null, true);
            return null;
        }
    }
}