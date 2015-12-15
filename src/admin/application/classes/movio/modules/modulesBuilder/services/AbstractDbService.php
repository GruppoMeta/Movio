<?php
class movio_modules_modulesBuilder_services_AbstractDbService extends GlizyObject
{
    protected $connection;
    protected $connectionNumber;

    public function __construct($connectionNumber)
    {
        $this->connectionNumber = $connectionNumber;
    }

    protected function setConnParam($name, $value)
    {
        __Config::set($name.'#'.$this->connectionNumber, $value);
    }

    public function connect($host, $port, $user, $psw, $dbname)
    {
        $this->setConnParam('DB_HOST', $host);
        if ($port) $this->setConnParam('DB_PORT', $port);
        $this->setConnParam('DB_USER', $user);
        $this->setConnParam('DB_PSW', $psw);
        $this->setConnParam('DB_NAME', $dbname);

        $this->connection = org_glizy_dataAccessDoctrine_DataAccess::getConnection($this->connectionNumber);
        return $this->connection->connect();
    }

    public function getTableNames()
    {
        $sm = org_glizy_ObjectFactory::createObject('org.glizy.dataAccessDoctrine.SchemaManager', $this->connection);
        return $sm->getTableNames();
    }

    public function getColumnNames($tableName)
    {
        $sm = org_glizy_ObjectFactory::createObject('org.glizy.dataAccessDoctrine.SchemaManager', $this->connection);
        return $sm->getColumnNames($tableName);
    }

    public function createRecordIterator($modelName, $tableName)
    {
        $model = org_glizy_ObjectFactory::createObject($modelName, $this->connectionNumber, $tableName);
        return $model->createRecordIterator();
    }
}