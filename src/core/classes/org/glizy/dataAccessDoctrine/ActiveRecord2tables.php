<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */



class org_glizy_dataAccessDoctrine_ActiveRecord2tables extends org_glizy_dataAccessDoctrine_ActiveRecord
{
    protected $detailTableName = null;
    protected $detailSequenceName = null;
    protected $detailPrimaryKeyName = null;
    protected $joinFields = array();
    protected $detailFieldsMap = array();
    protected $languageField = null;

    function __construct($connectionNumber=0)
    {
        parent::__construct($connectionNumber);
    }

    public function getBaseClassName()
    {
        return '2tables';
    }

    public function getDetailTableName()
    {
        return $this->detailTableName;
    }

    public function getDetailTableNameWithoutPrefix()
    {
        return substr( $this->getDetailTableName(), strlen( $this->tablePrefix ) );
    }

    function getSequenceName()
    {
        if (!$this->sequenceNameLoaded) {
            $this->loadSequenceName();
        }

        return $this->sequenceName;
    }

    function getDetailSequenceName()
    {
        if (!$this->sequenceNameLoaded) {
            $this->loadSequenceName();
        }

        return $this->detailSequenceName;
    }

    public function setDetailTableName($tableName, $prefix="")
    {
        $this->tablePrefix = $prefix;
        $this->detailTableName = $prefix.$tableName;
    }

    public function setDetailSequenceName($sequenceName)
    {
        $this->detailSequenceName = $sequenceName;
    }

    function getJoinFields()
    {
        return $this->joinFields;
    }

    function setJoinFields($theJoin1, $theJoin2)
    {
        $this->joinFields['mainTable']   = $theJoin1;
        $this->joinFields['detailTable'] = $theJoin2;
    }

    public function addField(org_glizy_dataAccessDoctrine_DbField $field, $isDetailField = false)
    {
        if ($isDetailField) {
            $this->detailFieldsMap[$field->name] = true;
        }

        $this->fields[$field->name] = $field;

        if ($field->key) {
            if (!$this->primaryKeyName && !$isDetailField) {
                $this->primaryKeyName = $field->name;
            }
            else if (!$this->detailPrimaryKeyName && $isDetailField) {
                $this->detailPrimaryKeyName = $field->name;
            } else if ($this->primaryKeyName) {
                throw org_glizy_dataAccessDoctrine_ActiveRecordException::primaryKeyAlreadyDefined($this->tableName);
            } else if ($this->detailPrimaryKeyName) {
                throw org_glizy_dataAccessDoctrine_ActiveRecordException::detailPrimaryKeyAlreadyDefined($this->tableName);
            }
        }
    }

    public function getDetailPrimaryKeyName()
    {
        return $this->detailPrimaryKeyName;
    }

    public function getLanguageId()
    {
        $editingLanguageId = org_glizy_ObjectValues::get('org.glizy', 'editingLanguageId');
        if (!is_null($editingLanguageId)) {
            return $editingLanguageId;
        }
        else {
            return org_glizy_ObjectValues::get('org.glizy', 'languageId');
        }
    }

    public function getLanguagesId()
    {
        $languagesId = org_glizy_ObjectValues::get('org.glizy', 'languagesId');
        return !is_null($languagesId) ? $languagesId : array($this->getLanguageId());
    }

    public function getLanguageField()
    {
        if ($this->detailFieldsMap[$this->languageField]) {
            return 'detail.'.$this->languageField;
        } else {
            return $this->languageField;
        }
    }

    public function setLanguageField($fieldName)
    {
        $this->languageField = $fieldName;
    }

    public function load($id, $idDetail = null)
    {
        if (is_null($this->primaryKeyName)) {
            throw org_glizy_dataAccessDoctrine_ActiveRecordException::primaryKeyNotDefined($this->tableName);
        }

        $qb = $this->createQueryBuilder()
                   ->select('*')
                   ->where($this->primaryKeyName.' = :id')
                   ->setParameter(':id', $id);

        if ($idDetail != null) {
            $qb->andWhere($this->detailPrimaryKeyName.' = :idDetail')
               ->setParameter(':idDetail', $idDetail);
        }

        $this->addLanguageFilter($qb);
        $this->addSiteFilter($qb);


        $r = $qb->execute()->fetch();

        if ($r) {
            $this->loadFromArray($r);
            $this->buildAllRelations();
            return true;
        } else {
            $this->emptyRecord();
            return false;
        }
    }

    public function save($values=NULL, $forceNew=false)
    {
        if (!is_null($values)) {
            $this->loadFromArray($values, true);
        }

        if (__Config::get('glizy.dataAccess.validate')) {
            $this->validate();
        }
       
        if ($this->processRelations) {
            $this->buildAllRelations();
            $this->saveAllRelations(true);
        }

        if (is_null($this->getId()) || $forceNew) {
            $result1 = $this->insertMain($values);
        }
        else {
            $result1 = $this->updateMain($values);
        }

        if (is_null($this->getDetailId()) || $forceNew) {
            $result2 = $this->insertDetail($values);
        }
        else {
            $result2 = $this->updateDetail($values);
        }

        if ($this->processRelations)  {
            $this->saveAllRelations(false);
        }

        return $result1;
    }

    protected function insertMain($values=NULL)
    {
        // sequenceName deve essere letto prima della insert
        // altrimenti può creare problemi con la insert del dettaglio
        $sequenceName = $this->getSequenceName();
        if (is_null($values)) {
            $values = get_object_vars($this->data);
        }

        $insertValues = array();
        $types = array();

        foreach ($values as $fieldName => $value) {
            $field = $this->fields[$fieldName];
            if (!$field->virtual && $this->detailFieldsMap[$fieldName] == false) {
                $insertValues[$fieldName] = $values[$fieldName];
                $types[] = $field->type;
            }
        }

        if ($this->siteField && !$this->detailFieldsMap[$this->siteField] && !isset($values[$this->siteField])) {
            $insertValues[$this->siteField] = $this->getSiteId();
            $types[] = $this->fields[$this->siteField]->type;
        }

        $r = $this->connection->insert($this->tableName, $insertValues, $types);

        if ($r != false) {
            $this->modifiedFields = array();
            $this->setId($this->connection->lastInsertId($sequenceName));
            return $this->getId();
        }
        else {
            return false;
        }
    }

    protected function updateMain($values=NULL)
    {
        if (is_null($values)) {
            $values = get_object_vars($this->data);
        }

        $insertValues = array();
        $types = array();

        foreach ($values as $fieldName => $value) {
            $field = $this->fields[$fieldName];
            if (isset($this->modifiedFields[$fieldName]) && !$field->virtual && $this->detailFieldsMap[$fieldName] == false) {
                $insertValues[$fieldName] = $values[$fieldName];
                $types[] = $field->type;
            }
        }

        if (!empty($insertValues)) {
            $identifier = array($this->primaryKeyName => $this->getId());
            $this->connection->update($this->tableName, $insertValues, $identifier, $types);
        }

        return $this->getId();
    }

    protected function insertDetail($values=NULL)
    {
        $sequenceName = $this->getDetailSequenceName();
        if (is_null($values)) {
            $values = get_object_vars($this->data);
        }

        $insertValues = array();
        $types = array();

        foreach ($values as $fieldName => $value) {
            $field = $this->fields[$fieldName];
            if (!$field->virtual && $this->detailFieldsMap[$fieldName] == true) {
                $insertValues[$fieldName] = $values[$fieldName];
                $types[] = $field->type;
            }
        }

        $detailFkField = $this->joinFields['detailTable'];
        if (is_null($insertValues[$detailFkField])) {
            $insertValues[$detailFkField] = $this->getId();
        }

        if ($this->siteField && $this->detailFieldsMap[$this->siteField] && !isset($values[$this->siteField])) {
            $insertValues[$this->siteField] = $this->getSiteId();
            $types[] = $this->fields[$this->siteField]->type;
        }

        if ($this->languageField && $this->detailFieldsMap[$this->languageField] && !$values[$this->languageField]) {
            $types[] = $this->fields[$this->languageField]->type;

            $languages = $this->getLanguagesId();

            foreach ($languages as $languageId) {
                $insertValues[$this->languageField] = $languageId;
                $r = $this->connection->insert($this->detailTableName, $insertValues, $types);

                if ($languageId == $this->getLanguageId()) {
                    if ($r != false) {
                        $this->modifiedFields = array();
                        $this->setDetailId($this->connection->lastInsertId($sequenceName));
                        $returnDetailId = $this->getDetailId();
                    }
                    else {
                        return false;
                    }
                }
            }

            return $returnDetailId;
        }

        $r = $this->connection->insert($this->detailTableName, $insertValues, $types);

        if ($r != false) {
            $this->modifiedFields = array();
            $this->setDetailId($this->connection->lastInsertId($sequenceName));
            return $this->getDetailId();
        }
        else {
            return false;
        }
    }

    protected function updateDetail($values=NULL)
    {
        if (is_null($values)) {
            $values = get_object_vars($this->data);
        }

        $insertValues = array();
        $types = array();

        foreach ($values as $fieldName => $value) {
            $field = $this->fields[$fieldName];
            if (isset($this->modifiedFields[$fieldName]) && !$field->virtual && $this->detailFieldsMap[$fieldName] == true) {
                $insertValues[$fieldName] = $values[$fieldName];
                $types[] = $field->type;
            }
        }

        if (!empty($insertValues)) {
            $identifier = array($this->detailPrimaryKeyName => $this->getDetailId());
            $this->connection->update($this->detailTableName, $insertValues, $identifier, $types);
        }
        return $this->getDetailId();
    }

    public function delete($id = null)
    {
        if (is_array($id)) {
            $identifier = $id;
        }
        else {
            $identifier = array($this->primaryKeyName => is_null($id) ? $this->getId() : $id);
        }

        $evt = array('type' => GLZ_EVT_AR_DELETE.'@'.$this->getClassName(), 'data' => $this);
        $this->dispatchEvent($evt);

        if ($this->processRelations)
        {
            if ($this->isNew()) {
                $this->load($id);
            }
            $this->buildAllRelations();
            $this->deleteAllRelations();
        }

        $this->emptyRecord();

        $detailId = array($this->joinFields['detailTable'] => $identifier[$this->primaryKeyName] );
        $this->connection->delete($this->detailTableName, $detailId);

        return $this->connection->delete($this->tableName, $identifier);
    }

    public function getDetailId()
    {
        if (is_null($this->detailPrimaryKeyName)) {
            throw org_glizy_dataAccessDoctrine_ActiveRecordException::detailPrimaryKeyNotDefined($this->tableName);
        }
        $detailPrimaryKeyName = $this->detailPrimaryKeyName;
        return $this->$detailPrimaryKeyName;
    }

    public function setDetailId($value)
    {
        $detailPrimaryKeyName = $this->detailPrimaryKeyName;
        $this->$detailPrimaryKeyName = $value;
    }

    public function createRecordIterator() {
        return new org_glizy_dataAccessDoctrine_RecordIterator2tables($this);
    }

    public function createQueryBuilder($addFrom=true, $tableAlias='t1', $tableDetailAlias='detail') {
        $qb = $this->connection->createQueryBuilder();

        if ($addFrom) {
            $qb->from($this->tableName, $tableAlias)
               ->join($tableAlias, $this->detailTableName, $tableDetailAlias,
                      $qb->expr()->eq($tableAlias.'.'.$this->joinFields['mainTable'], $tableDetailAlias.'.'. $this->joinFields['detailTable']));
        }

        return $qb;
    }

    protected function addLanguageFilter($qb)
    {
        if ($this->languageField) {
            $qb->andWhere($qb->expr()->eq($this->languageField, ':language'))
               ->setParameter(':language', $this->getLanguageId());
        }
    }

    protected function addSiteFilter($qb)
    {
        if ($this->siteField) {
            $qb->andWhere($qb->expr()->eq($this->siteField, ':site'))
               ->setParameter(':site', $this->getSiteId());
        }
    }

    private function loadSequenceName()
    {
        $this->sequenceNameLoaded = true;
        $sm = new org_glizy_dataAccessDoctrine_SchemaManager($this->connection);
        $sequenceName = $sm->getSequenceName($this->getTableName());
        $this->setSequenceName($sequenceName);

        $sm = new org_glizy_dataAccessDoctrine_SchemaManager($this->connection);
        $detailSequenceName = $sm->getSequenceName($this->getDetailTableName());
        $this->setDetailSequenceName($detailSequenceName);
    }
}