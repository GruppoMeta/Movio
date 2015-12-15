<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

use Doctrine\DBAL\Types\Type;

class org_glizy_dataAccessDoctrine_RecordIteratorSimpleDocument extends org_glizy_dataAccessDoctrine_RecordIterator
{
    const DOCUMENT_TABLE_ALIAS = 'doc';
    const DOCUMENT_TABLE_TYPE = 'simple_document_type';

    protected $indexNumber;
    protected $conditionsMap;

    protected function resetQuery()
    {
        $this->qb = $this->ar->createQueryBuilder(true, self::DOCUMENT_TABLE_ALIAS);
        $this->qb->select(self::DOCUMENT_TABLE_ALIAS.'.*')
                 ->where($this->expr->eq(self::DOCUMENT_TABLE_ALIAS.'.'.self::DOCUMENT_TABLE_TYPE, ':type'))
                 ->setParameter(":type", $this->ar->getType());
        $this->indexNumber = 0;
        $this->hasLimit = false;
        $this->hasSelect = true;
        $this->conditionsMap = array();
    }

    protected function whereCondition($fieldName, $value, $condition = '=', $composite = null)
    {
        $indexType = $this->ar->getIndexFieldType($fieldName);
        $indexAlias = 'index'.$this->indexNumber++;

        $documentId = $this->ar->getDocumentTableIdName();

        $documentIndexTablePrefix = $this->ar->getDocumentIndexTablePrefix();
        $indexTablePrefix = $documentIndexTablePrefix.$indexType;

        $documentIndexFieldPrefix = $this->ar->getDocumentIndexFieldPrefix();
        $indexFieldPrefixAlias = $indexAlias.'.'.$documentIndexFieldPrefix.$indexType;

        $this->qb->join(self::DOCUMENT_TABLE_ALIAS, $indexTablePrefix.'_tbl', $indexAlias,
                        $this->expr->eq(self::DOCUMENT_TABLE_ALIAS.'.'.$documentId, $indexFieldPrefixAlias.'_FK_simple_document_id'));

        $and = $this->expr->andX();
        $and->add($this->expr->eq("{$indexFieldPrefixAlias}_name", ":name{$this->indexNumber}"));

        $fieldType = $this->ar->getField($fieldName)->type;

        $valueColumn = "{$indexFieldPrefixAlias}_value";
        $valueParam =  ":value{$this->indexNumber}";

        $and->add($this->expr->comparison($valueColumn, $condition, $valueParam));

        $this->qb->setParameter(":name{$this->indexNumber}", $fieldName);
        $this->qb->setParameter(":value{$this->indexNumber}", $value);

        $this->qb->andWhere($and);

        $this->conditionsMap[$fieldName] = $indexFieldPrefixAlias;

        return $this;
    }

    public function orderBy($fieldName, $order = 'ASC')
    {
        if (is_null($this->conditionsMap[$fieldName])) {
            $indexAlias = 'index'.$this->indexNumber++;
            $indexType = $this->ar->getIndexFieldType($fieldName);

            $documentId = $this->ar->getDocumentTableIdName();

            $documentIndexTablePrefix = $this->ar->getDocumentIndexTablePrefix();
            $indexTablePrefix = $documentIndexTablePrefix.$indexType;

            $documentIndexFieldPrefix = $this->ar->getDocumentIndexFieldPrefix();
            $indexFieldPrefixAlias = $indexAlias.'.'.$documentIndexFieldPrefix.$indexType;

            $this->qb->join(self::DOCUMENT_TABLE_ALIAS, $indexTablePrefix.'_tbl', $indexAlias, self::DOCUMENT_TABLE_ALIAS.".{$documentId} = {$indexFieldPrefixAlias}_FK_simple_document_id");
            $this->qb->andWhere($this->expr->eq("{$indexFieldPrefixAlias}_name", ":name{$this->indexNumber}"));
            $this->qb->setParameter(":name{$this->indexNumber}", $fieldName);
        }
        else {
            $indexFieldPrefixAlias = $this->conditionsMap[$fieldName];
        }

        $this->qb->addOrderBy("{$indexFieldPrefixAlias}_value", $order);

        return $this;
    }

    // TODO
    public function execSql($sql, $options=array())
    {
        // lanciare eccezione
    }
}