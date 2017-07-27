<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

use Doctrine\DBAL\Types\Type;

class org_glizy_dataAccessDoctrine_RecordIterator extends org_glizy_dataAccessDoctrine_AbstractRecordIterator
{
    protected $conditionNumber;

    protected function resetQuery()
    {
        parent::resetQuery();
        $this->qb = $this->ar->createQueryBuilder(true);
        $this->conditionNumber = 0;
        $this->hasLimit = false;
        $this->hasSelect = false;
    }

    protected function processUnionFields($fieldName, $value)
    {
        if (strpos($fieldName, ',') !== false) {
            if (!empty( $value )) {
                $fields = explode(',', $fieldName);

                $v = array();
                foreach ($fields as $field) {
                    $v[] = array('field' => $field, 'value' => '%'.$value.'%', 'condition' => 'LIKE' );
                }
                $value = $v;
            }
        } else if (!$this->ar->fieldExists($fieldName)) {
            return '';
        }

        return $value;
    }

    public function execSql($sql, $options=array())
    {
        if (is_string($sql)) {
            $sql = array('sql' => $sql);
        }

// TODO controllare se nella query c'è già l'order
        $orderBy = $this->qb->getQueryPart('orderBy');
        $sql['sql'] .= $orderBy ? ' ORDER BY ' . implode(', ', $orderBy) : '';

        if (__Config::get('MULTISITE_ENABLED') && $this->ar->getSiteField()) {
            $siteField = $this->ar->getSiteField();
            $siteId = $this->ar->getSiteId();

            preg_match_all('/WHERE(.*?)( FROM | ORDER | GROUP |$)/si', $sql['sql'], $m);
            $lastMatch = count($m[0]) - 1;
            $sql['sql'] = str_replace($m[1][$lastMatch], ' '.$siteField.' = '.$siteId.' AND ('.$m[1][$lastMatch].') ', $sql['sql']);
        }

        if (isset($sql['filters']) && count($sql['filters'])) {
            $filtersSql = implode(' AND ', $sql['filters']);
            preg_match_all('/WHERE(.*?)( FROM | ORDER | GROUP |$)/si', $sql['sql'], $m);
            $lastMatch = count($m[0]) - 1;
            $sql['sql'] = str_replace($m[1][$lastMatch], '('.$m[1][$lastMatch].') AND ('.$filtersSql.') ', $sql['sql']);
        }

        if (isset($options['replace'])) {
            foreach ($options['replace'] as $k => $v) {
                $sql['sql'] = str_replace($k, $v, $sql['sql']);
            }
        }

        $params = isset($options['params']) ? $options['params'] : ( is_array($options) ? $options : array());
        $params = isset($sql['params']) ? array_merge($sql['params'], $params) : $params;

        $connection = $this->ar->getConnection();


// ($this->sqlParts['orderBy'] ? ' ORDER BY ' . implode(', ', $this->sqlParts['orderBy']) : '');
        $this->statement = $connection->executeQuery($sql['sql'], $params);
// TODO implementare meglio
        $this->count = $this->statement->rowCount();

// TODO implementare meglio
        $firstResult = $this->qb->getFirstResult();
        $maxResults = $this->qb->getMaxResults();
        if (!is_null($firstResult) && !is_null($maxResults)) {
            $sql['sql'] = $connection->getDatabasePlatform()->modifyLimitQuery($sql['sql'], $maxResults, $firstResult);
            $this->statement = $connection->executeQuery($sql['sql'], $params);
        }
    }

    protected function whereCondition($fieldName, $value, $condition = null, $composite = null)
    {
        $valueParam = ":value".$this->conditionNumber++;

        if (is_null($condition)) {
            if (is_null($composite)) {
                $this->qb->andWhere($this->expr->eq($fieldName, $valueParam));
            }
            else {
                $composite->add($this->expr->eq($fieldName, $valueParam));
            }
        }
        else {
            $fieldType = $this->ar->getFieldType($fieldName);
            $cast = $fieldType == Type::DATE || $fieldType == Type::DATETIME;

            if (is_null($composite)) {
                $this->qb->andWhere($this->expr->comparison($fieldName, $condition, $valueParam, $cast));
            }
            else {
                $composite->add($this->expr->comparison($fieldName, $condition, $valueParam, $cast));
            }
        }

        $this->qb->setParameter($valueParam, $value);

        return $this;
    }

    public function orderBy($fieldName, $order = 'ASC')
    {
        $this->qb->addOrderBy($fieldName, $order);
        return $this;
    }

    public function groupBy($groupBy)
    {
        $this->qb->groupBy($groupBy);
        return $this;
    }

    public function exec()
    {
        if (!$this->querySqlToExec && !$this->hasSelect) {
            $this->select('*');
        }

        parent:: exec();
    }
}