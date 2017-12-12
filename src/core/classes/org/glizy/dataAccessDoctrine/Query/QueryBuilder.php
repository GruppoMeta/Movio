<?php

use Doctrine\DBAL\Query\Expression\CompositeExpression,
    Doctrine\DBAL\Connection,
    Doctrine\DBAL\Cache\QueryCacheProfile;

class org_glizy_dataAccessDoctrine_Query_QueryBuilder extends \Doctrine\DBAL\Query\QueryBuilder
{
    public function quoteSelectTerm($term) {
        $connection = $this->getConnection();
        if (preg_match('/(\w+)\((\w+) (\w+)\) as (\w+)/i', $term, $m)) {
            return $m[1].'('.$m[2].' '.$connection->quoteIdentifier($m[3]).') as '.$connection->quoteIdentifier($m[4]);
        } else if (preg_match('/DISTINCT ON\((.+)\)(.+)/i', $term, $m)) {
            return 'DISTINCT ON('. $connection->quoteIdentifier($m[1]).')'.$m[2];
        } elseif (preg_match('/DISTINCT (.+) as (.+)/i', $term, $m)) {
            return 'DISTINCT '. $connection->quoteIdentifier($m[1]) . ' as ' . $connection->quoteIdentifier($m[2]);
        } elseif (preg_match('/DISTINCT (.+)/i', $term, $m)) {
            return 'DISTINCT '. $connection->quoteIdentifier($m[1]);
        } elseif (preg_match('/MATCH\s*(.+)\s*AGAINST\s*(.+)/i', $term, $m)) {
            return $term;
        } elseif (preg_match('/(.+) as (.+)/', $term, $m)) {
            return $connection->quoteIdentifier($m[1]) . ' as ' . $connection->quoteIdentifier($m[2]);
        } else {
            return $connection->quoteIdentifier($term);
        }
    }

    public function add($sqlPartName, $sqlPart, $append = false)
    {
        if ($sqlPartName == 'select') {
            if (is_array($sqlPart)) {
                $i = 0;
                foreach ($sqlPart as $term) {
                    if ($term instanceof org_glizy_dataAccessDoctrine_Query_Expression_SqlExpression) {
                        $sqlPart[$i] = $term;
                    } else {
                        $sqlPart[$i] = $this->quoteSelectTerm($term);
                    }
                    $i++;
                }
            }
            else {
                $sqlPart = $this->quoteSelectTerm($sqlPart);
            }
        }

        return parent::add($sqlPartName, $sqlPart, $append);
    }

    protected function getSQLForSelect()
    {
        $connection = $this->getConnection();
        $query = 'SELECT ' . implode(', ', $this->sqlParts['select']) . ' FROM ';

        $fromClauses = array();

        // Loop through all FROM clauses
        foreach ($this->sqlParts['from'] as $from) {
            if ($from['table'] instanceof org_glizy_dataAccessDoctrine_Query_Expression_SqlExpression) {
                $fromClause = $from['table']. ' ' . $connection->quoteIdentifier($from['alias']);
            } else {
                $fromClause = $connection->quoteIdentifier($from['table']) . ' ' . $connection->quoteIdentifier($from['alias']);
            }

            if (isset($this->sqlParts['join'][$from['alias']])) {
                foreach ($this->sqlParts['join'][$from['alias']] as $join) {
                    $fromClause .= ' ' . strtoupper($join['joinType'])
                                 . ' JOIN ' . $connection->quoteIdentifier($join['joinTable']) . ' ' . $connection->quoteIdentifier($join['joinAlias'])
                                 . ' ON ' . ((string) $join['joinCondition']);
                }
            }

            $fromClauses[$from['alias']] = $fromClause;
        }

        // loop through all JOIN clasues for validation purpose
        foreach ($this->sqlParts['join'] as $fromAlias => $joins) {
            if ( ! isset($fromClauses[$fromAlias]) ) {
                throw Doctrine\DBAL\Query\QueryException::unknownFromAlias($fromAlias, array_keys($fromClauses));
            }
        }

        $query .= implode(', ', $fromClauses)
                . ($this->sqlParts['where'] !== null ? ' WHERE ' . ((string) $this->sqlParts['where']) : '')
                . ($this->sqlParts['groupBy'] ? ' GROUP BY ' . implode(', ', $this->sqlParts['groupBy']) : '')
                . ($this->sqlParts['having'] !== null ? ' HAVING ' . ((string) $this->sqlParts['having']) : '')
                . ($this->sqlParts['orderBy'] ? ' ORDER BY ' . implode(', ', $this->sqlParts['orderBy']) : '');

        return ($this->maxResults === null && $this->firstResult == null)
            ? $query
            : $connection->getDatabasePlatform()->modifyLimitQuery($query, $this->maxResults, $this->firstResult);
    }

    /**
     * Converts this instance into an UPDATE string in SQL.
     *
     * @return string
     */
    protected function getSQLForUpdate()
    {
        $connection = $this->getConnection();
        $table = $this->sqlParts['from']['table'] . ($this->sqlParts['from']['alias'] ? ' ' . $this->sqlParts['from']['alias'] : '');

        $query = 'UPDATE ' . $connection->quoteIdentifier($table)
               . ' SET ' . implode(", ", $this->sqlParts['set'])
               . ($this->sqlParts['where'] !== null ? ' WHERE ' . ((string) $this->sqlParts['where']) : '');

        return $query;
    }

    /**
     * Converts this instance into a DELETE string in SQL.
     *
     * @return string
     */
    protected function getSQLForDelete()
    {
        $connection = $this->getConnection();
        $table = $this->sqlParts['from']['table'] . ($this->sqlParts['from']['alias'] ? ' ' . $this->sqlParts['from']['alias'] : '');
        $query = 'DELETE FROM ' . $connection->quoteIdentifier($table) . ($this->sqlParts['where'] !== null ? ' WHERE ' . ((string) $this->sqlParts['where']) : '');

        return $query;
    }

    /**
     * Specifies an ordering for the query results.
     * Replaces any previously specified orderings, if any.
     *
     * @param string $sort The ordering expression.
     * @param string $order The ordering direction.
     * @return QueryBuilder This QueryBuilder instance.
     */
    public function orderBy($sort, $order = null)
    {
        $connection = $this->getConnection();
        return parent::orderBy($connection->quoteIdentifier($sort), $order);
    }

    /**
     * Adds an ordering to the query results.
     *
     * @param string $sort The ordering expression.
     * @param string $order The ordering direction.
     * @return QueryBuilder This QueryBuilder instance.
     */
    public function addOrderBy($sort, $order = null)
    {
        $connection = $this->getConnection();
        return parent::addOrderBy($connection->quoteIdentifier($sort), $order);
    }


    public function groupBy($groupBy)
    {
        $connection = $this->getConnection();
        return parent::groupBy($connection->quoteIdentifier($groupBy));
    }

    /**
     * Execute this query using the bound parameters and their types.
     *
     * Uses {@see Connection::executeQuery} for select statements and {@see Connection::executeUpdate}
     * for insert, update and delete statements.
     *
     * @return mixed
     */
    public function execute()
    {
        $connection = $this->getConnection();
        if ($this->type == self::SELECT) {
            // TODO migliorare creando un QuerybuiBuilderCache
            if (__Config::get('QUERY_CACHING') && ($cacheDriver = org_glizy_dataAccessDoctrine_DataAccess::getCache())) {
                $lifeTime = __Config::get('QUERY_CACHING_LIFETIME');
                $sql = $this->getSQL();
                $key = md5($sql);
                return $connection->executeQuery($this->getSQL(), $this->params, $this->paramTypes, new QueryCacheProfile($lifeTime, $key, $cacheDriver));
            } else {
                return $connection->executeQuery($this->getSQL(), $this->params, $this->paramTypes);
            }
        } else {
            return $connection->executeUpdate($this->getSQL(), $this->params, $this->paramTypes);
        }
    }
}
