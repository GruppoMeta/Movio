<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

use Doctrine\DBAL\Types\Type;

class org_glizy_dataAccessDoctrine_SqlRecordIterator extends GlizyObject implements Iterator
{
    protected $connectionNumber;
    protected $querySqlToExec = [];
    protected $returnClass;
    protected $EOF = false;
    protected $lastParams = NULL;
    protected $lastQuery = '';
    protected $pos = 0;
    protected $statement = NULL;
    protected $data = NULL;
    protected $count = NULL;
    protected $hasLimit = false;


    /**
     * @param int $connectionNumber
     */
    function __construct($connectionNumber=0)
    {
        $this->connection = org_glizy_dataAccessDoctrine_DataAccess::getConnection($connectionNumber);
    }

    /**
     * @param string $query
     * @param array $params
     *
     * @return $this
     *
     * @throws \BadMethodCallException
     */
    public function load($query, $params=null)
    {
        if (method_exists($this, $query)) {
            $this->querySqlToExec = org_glizy_helpers_PhpScript::callMethodWithParams($this, $query, $params);
        } else {
            throw new \BadMethodCallException("The query do not exists '".$query."'");
        }

        $this->data = null;
        $this->returnClass = null;
        $this->pos = 0;
        $this->EOF = false;
        $this->lastQuery = $query;
        $this->lastParams = null;
        $this->count = NULL;
        $this->statement = NULL;

        return $this;
    }

    /**
     * @return $this
     */
    public function exec()
    {
        $this->execSql($this->querySqlToExec, $this->lastParams);
        return $this;
    }

    /**
     * @param string $sql
     * @param array $options
     */
    protected function execSql($sql, $options=array())
    {
        if (is_string($sql)) {
            $sql = array('sql' => $sql);
        }

        $params = isset($options['params']) ? $options['params'] : ( is_array($options) ? $options : array());
        $params = isset($sql['params']) ? array_merge($sql['params'], $params) : $params;

// TODO controllare se nella query c'è già l'order
        // $orderBy = $this->qb->getQueryPart('orderBy');
        // $sql['sql'] .= $orderBy ? ' ORDER BY ' . implode(', ', $orderBy) : '';

        if (!empty($sql['filters'])) {
            $index = 0;
            $filtersSql = array();
            foreach($sql['filters'] as $k=>$v) {
                $condition = is_array($v) ? $v['condition'] : '=';
                $value = is_array($v) ? $v['value'] : $v;
                $paramName = ':params'.$index++;
                $filtersSql[] = $k.$condition.$paramName;
                $params[$paramName] = $value;
            }

            $filtersSql = implode(' AND ', $filtersSql);
            preg_match_all('/WHERE(.*?)( FROM | ORDER | GROUP |$)/si', $sql['sql'], $m);
            $lastMatch = count($m[0]) - 1;
            if ($lastMatch <= 0) {
                $sql['sql'] .= ' WHERE 1=1 ';
                preg_match_all('/WHERE(.*?)( FROM | ORDER | GROUP |$)/si', $sql['sql'], $m);
                $lastMatch = count($m[0]) - 1;
            }
            $sql['sql'] = str_replace($m[1][$lastMatch], '('.$m[1][$lastMatch].') AND ('.$filtersSql.') ', $sql['sql']);
        }


// ($this->sqlParts['orderBy'] ? ' ORDER BY ' . implode(', ', $this->sqlParts['orderBy']) : '');
        $this->statement = $this->connection->executeQuery($sql['sql'], $params);
// TODO implementare meglio
        $this->count = $this->statement->rowCount();

        $this->returnClass = isset($sql['return']) ? $sql['return'] : '';

        if ($this->hasLimit) {
            $stmt = $this->connection->executeQuery('SELECT FOUND_ROWS() as tot;');
            $row = $stmt->fetch();
            $this->count = (int)$row['tot'];
        }
    }

    public function next()
    {
        $this->fetch();
    }

    public function rewind()
    {
        if ($this->pos == 0) {
            $this->exec();
        } else if ($this->pos == 1) {
            // noting
            return;
        } else {
            $this->execSql($this->querySqlToExec, $this->lastParams);
        }

        $this->pos = 0;
        $this->fetch();
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return !$this->EOF;
    }

    /**
     * @return int
     */
    public function key()
    {
        return $this->recordPos();
    }

    /**
     * @param bool $returnNewIfNull
     *
     * @return GlizyObject|mixed|null|StdClass
     */
    public function &first($returnNewIfNull=false)
    {
        return $this->current();
    }

    /**
     * @return GlizyObject|mixed|null|StdClass
     */
    public function &current()
    {
        if ($this->data == NULL) {
            $this->rewind();
        }

        // se non ci sono record
        if ($this->EOF) {
            $r = NULL;
            return $r;
        }

        if (!$this->returnClass) {
            $r = new StdClass;
            foreach($this->data as $k=>$v) {
                $r->{$k} = $v;
            }
        } else {
           $r = __ObjectFactory::createObject($this->returnClass, $this->data);
        }

        return $r;
    }

    /**
     * @return mixed
     */
    public function count()
    {
        if ($this->statement == NULL) {
            $this->rewind();
        }

        return $this->count == NULL ? $this->statement->rowCount() : $this->count;
    }

    /**
     * @param $v
     */
    public function setCount( $v )
    {
        $this->count = $v;
    }

    /**
     * @return int
     */
    public function recordPos()
    {
        return $this->pos;
    }

    /**
     * @return string
     */
    public function getArType()
    {
        return 'table';
    }

    /**
     * @param  string $fieldName
     * @param  string $order
     * @return org_glizy_dataAccessDoctrine_SqlRecordIterator
     */
    public function orderBy($fieldName, $order = 'ASC')
    {
        if (is_array($this->querySqlToExec) && stripos($this->querySqlToExec['sql'], ' ORDER BY ')!==false) {
            throw new \Exception("The query already have order '".$this->querySqlToExec['sql']."'");
        }

        $this->querySqlToExec['sql'] .= ' ORDER BY '. $fieldName.' '.$order;
        return $this;
    }

    /**
     * @param  int|array  $offset
     * @param  integer $limit
     * @param  boolean $performCount
     * @return org_glizy_dataAccessDoctrine_SqlRecordIterator
     */
    public function limit($offset, $limit = -1, $performCount=true)
    {
        if (is_array($this->querySqlToExec) && stripos($this->querySqlToExec['sql'], ' LIMIT ')!==false) {
            throw new \Exception("The query already have limit '".$this->querySqlToExec['sql']."'");
        }

        if (is_array($offset)) {
            if (!isset($offset['start'])) {
                list($offset, $limit) = array_values($offset);
            } else {
                $limit = $offset['pageLength'];
                $offset = $offset['start'];
            }
        }
        $this->querySqlToExec['sql'] .= ' LIMIT '.$offset.', '.$limit;
        $this->hasLimit = $performCount;

        if ( $performCount && stripos( $this->querySqlToExec['sql'], 'SELECT SQL_CALC_FOUND_ROWS' ) === false ) {
            $pos = stripos($this->querySqlToExec['sql'], 'SELECT' );
            $this->querySqlToExec['sql'] = 'SELECT SQL_CALC_FOUND_ROWS '.substr($this->querySqlToExec['sql'], $pos + 7 );
        }
        return $this;
    }


    private function fetch()
    {
        $this->data = $this->statement->fetch();
        $this->EOF = $this->data === false;
        $this->pos++;

        if ($this->EOF) {
            $this->statement->closeCursor();
        }
    }
}