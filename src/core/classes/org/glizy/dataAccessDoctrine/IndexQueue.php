<?php
class org_glizy_dataAccessDoctrine_IndexQueue
{
    protected $queryLength = 50000;
    protected $connection;
    protected $query = array();

    public function __construct($connection)
    {
        $this->connection = $connection;
    }

    public function insert($tableName, array $data, array $types = array())
    {
        if (!isset($this->query[$tableName])) {
            $tableNameQuoted = $this->connection->quoteIdentifier($tableName);
            $cols = $this->connection->quoteIdentifiers(array_keys($data));

            $sql = 'INSERT INTO ' . $tableNameQuoted
                   . ' (' . implode(', ', $cols) . ') VALUES ';

            $this->query[$tableName] = array();
            $this->query[$tableName]['insert'] = $sql;
            $this->query[$tableName]['insertLength'] = strlen($sql);
            $this->query[$tableName]['values'] = array();
        }

        $part = '(' . implode(', ', array_values($data)) . ')';
        if ($this->query[$tableName]['insertLength']+strlen($part)>$this->queryLength) {
            $this->execute();
        }
        $this->query[$tableName]['values'][] = $part;
    }

    public function execute()
    {
        foreach ($this->query as $tableName => $q) {
            $sql = $q['insert'];
            $sql .= implode(', ', $q['values']);
            $this->connection->executeQuery($sql);
            $this->query[$tableName]['values'] = array();
        }
    }
}