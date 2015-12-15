<?php
// TODO: mettere 11 in una variablie  config
class movio_modules_modulesBuilder_services_PgsqlService extends movio_modules_modulesBuilder_services_AbstractDbService
{
    public function connect($host, $port, $user, $psw, $dbname)
    {
        $this->setConnParam('DB_TYPE', 'pgsql');
        return parent::connect($host, $port, $user, $psw, $dbname);
    }
}