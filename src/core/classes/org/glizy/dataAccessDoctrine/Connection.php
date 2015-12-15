<?php

use Doctrine\DBAL\Driver,
    Doctrine\DBAL\Configuration,
    Doctrine\Common\EventManager;

// classe Connection che quota gli identificatori nelle query
class org_glizy_dataAccessDoctrine_Connection extends \Doctrine\DBAL\Connection
{
    protected static $mapIdentifier = array();
        
    /**
     * Initializes a new instance of the Connection class.
     *
     * @param array $params  The connection parameters.
     * @param Driver $driver
     * @param Configuration $config
     * @param EventManager $eventManager
     */
    public function __construct(array $params, Driver $driver, Configuration $config = null, EventManager $eventManager = null)
    {
        parent::__construct($params, $driver, $config, $eventManager);
        $this->_expr = new org_glizy_dataAccessDoctrine_Query_Expression_ExpressionBuilder($this);
    }
    
    /**
     * Executes an SQL DELETE statement on a table.
     *
     * @param string $tableName The name of the table on which to delete.
     * @param array $identifier The deletion criteria. An associative array containing column-value pairs.
     * @return integer The number of affected rows.
     */
    public function delete($tableName, array $identifier)
    {
        $this->connect();

        $criteria = array();

        foreach (array_keys($identifier) as $columnName) {
            $criteria[] = $this->quoteIdentifier($columnName) . ' = ?';
        }

        $query = 'DELETE FROM ' . $this->quoteIdentifier($tableName) . ' WHERE ' . implode(' AND ', $criteria);

        return $this->executeUpdate($query, array_values($identifier));
    }

    /**
     * Executes an SQL UPDATE statement on a table.
     *
     * @param string $tableName The name of the table to update.
     * @param array $data
     * @param array $identifier The update criteria. An associative array containing column-value pairs.
     * @param array $types Types of the merged $data and $identifier arrays in that order.
     * @return integer The number of affected rows.
     */
    public function update($tableName, array $data, array $identifier, array $types = array())
    {
        $this->connect();
        $set = array();
        foreach ($data as $columnName => $value) {
            $set[] = $this->quoteIdentifier($columnName) . ' = ?';
        }

        $params = array_merge(array_values($data), array_values($identifier));
        
        $quotedIdentifier = $this->quoteIdentifiers(array_keys($identifier));

        $sql  = 'UPDATE ' . $this->quoteIdentifier($tableName) . ' SET ' . implode(', ', $set)
                . ' WHERE ' . implode(' = ? AND ', $quotedIdentifier)
                . ' = ?';

        return $this->executeUpdate($sql, $params, $types);
    }

    /**
     * Inserts a table row with specified data.
     *
     * @param string $tableName The name of the table to insert data into.
     * @param array $data An associative array containing column-value pairs.
     * @param array $types Types of the inserted data.
     * @return integer The number of affected rows.
     */
    public function insert($tableName, array $data, array $types = array())
    {
        $this->connect();

        // column names are specified as array keys
        $cols = array();
        $placeholders = array();

        foreach ($data as $columnName => $value) {
            $cols[] = $this->quoteIdentifier($columnName);
            $placeholders[] = '?';
        }

        $query = 'INSERT INTO ' . $this->quoteIdentifier($tableName)
               . ' (' . implode(', ', $cols) . ')'
               . ' VALUES (' . implode(', ', $placeholders) . ')';

        return $this->executeUpdate($query, array_values($data), $types);
    }
    
    /**
     * Quote a string so it can be safely used as a table or column name, even if
     * it is a reserved name.
     *
     * Delimiting style depends on the underlying database platform that is being used.
     *
     * NOTE: Just because you CAN use quoted identifiers does not mean
     * you SHOULD use them. In general, they end up causing way more
     * problems than they solve.
     *
     * @param string $str The name to be quoted.
     * @return string The quoted name.
     */
    public function quoteIdentifier($str)
    {
        if (isset(self::$mapIdentifier[$str])) {
            return self::$mapIdentifier[$str];
        }
        
        $upStr = strtoupper($str);
        
        if ($str == '*' || $str[0] == ':' || $str[0] == "'" || strpos($upStr, 'DISTINCT') === 0 || strpos($upStr, 'COUNT') === 0) {
            self::$mapIdentifier[$str] = $str;
        }
        else if (strpos($str, '.') !== false) {
            $parts = array_map(array($this, "quoteIdentifier"), explode(".", $str));
            self::$mapIdentifier[$str] = implode(".", $parts);
        }
        else {
            self::$mapIdentifier[$str] = parent::quoteIdentifier($str);
        }
        
        return self::$mapIdentifier[$str];
    }
    
    /**
     * @param array $identifiers The array of names to be quoted.
     * @return array The quoted names.
     */
    public function quoteIdentifiers($identifiers)
    {
        $result = array();
        foreach ($identifiers as $s) {
            $result[] = $this->quoteIdentifier($s);
        }
        return $result;
    }

    /**
     * Create a new instance of a SQL query builder.
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function createQueryBuilder()
    {
        return new org_glizy_dataAccessDoctrine_Query_QueryBuilder($this);
    }
}