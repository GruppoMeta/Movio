<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

use Doctrine\DBAL\Types\Type;

class org_glizy_dataAccessDoctrine_ActiveRecordSimpleDocument extends org_glizy_dataAccessDoctrine_ActiveRecord
{
    const DOCUMENT_TABLE_NAME = 'simple_documents_tbl';
    const DOCUMENT_TABLE_ID = 'simple_document_id';
    const DOCUMENT_TABLE_TYPE = 'simple_document_type';
    const DOCUMENT_FK_SITE_ID = 'simple_document_FK_site_id';
    const DOCUMENT_TABLE_OBJECT = 'simple_document_object';
    const DOCUMENT_INDEX_TABLE_PREFIX = 'simple_documents_index_';
    const DOCUMENT_INDEX_FIELD_PREFIX = 'simple_document_index_';
    const DOCUMENT_BASE_PREFIX = 'simple_document_';

    protected $type;
    protected static $typeMap = array(Type::INTEGER => 'int',
                                      Type::SMALLINT => 'int',
                                      Type::BIGINT => 'int',
                                      Type::STRING => 'text',
                                      Type::TEXT => 'text',
                                      Type::TARRAY => 'text',
                                      Type::DATE => 'date',
                                      Type::DATETIME => 'datetime',
                                      Type::TIME => 'time');

    function __construct($connectionNumber=0)
    {
        parent::__construct($connectionNumber);

        $this->addField(new org_glizy_dataAccessDoctrine_SystemField(
                                self::DOCUMENT_TABLE_ID,
                                Doctrine\DBAL\Types\Type::INTEGER,
                                10,
                                true,
                                null,
                                NULL
                            )
                        );

        $this->addField(new org_glizy_dataAccessDoctrine_SystemField(
                                self::DOCUMENT_TABLE_TYPE,
                                Doctrine\DBAL\Types\Type::STRING,
                                255,
                                false,
                                null,
                                0,
                                true,
                                false,
                                '',
                                org_glizy_dataAccessDoctrine_DbField::NOT_INDEXED
                            )
                        );

        if (__Config::get('MULTISITE_ENABLED')) {
            $this->addField(new org_glizy_dataAccessDoctrine_SystemField(
                            self::DOCUMENT_FK_SITE_ID,
                            Doctrine\DBAL\Types\Type::INTEGER,
                            10,
                            false,
                            null,
                            NULL
                        )
                    );
        }
    }

    public function addField(org_glizy_dataAccessDoctrine_DbField $field )
    {
        parent::addField($field);

        if ($field->name == self::DOCUMENT_FK_SITE_ID) {
            $this->setSiteField(self::DOCUMENT_FK_SITE_ID);
        }
    }

    public function getType()
    {
        return $this->{self::DOCUMENT_TABLE_TYPE};
    }

    public function setType($type)
    {
        $this->type = $type;
        $this->{self::DOCUMENT_TABLE_TYPE} = $type;
    }

    function getSequenceName()
    {
        if (!$this->sequenceNameLoaded) {
            $this->loadSequenceName();
        }

        return $this->sequenceName;
    }

    public function getDocumentTableName()
    {
        return $this->tablePrefix.self::DOCUMENT_TABLE_NAME;
    }

    function getDocumentTableIdName()
    {
        return self::DOCUMENT_TABLE_ID;
    }

    function getDocumentIndexTablePrefix()
    {
        return $this->tablePrefix.self::DOCUMENT_INDEX_TABLE_PREFIX;
    }

    function getDocumentIndexFieldPrefix()
    {
        return self::DOCUMENT_INDEX_FIELD_PREFIX;
    }

    function getIndexFieldType($fieldName)
    {
        $field = $this->fields[$fieldName];

        if ($field->index == $field::FULLTEXT) {
            return 'fulltext';
        } else {
            $type = $this->fields[$fieldName]->type;
            return self::$typeMap[$type];
        }
    }

    public function load($id)
    {
        if (empty($id)) {
            $this->emptyRecord();
            return false;
        }

        $qb = $this->createQueryBuilder();

        $qb->select('*')
            ->where($qb->expr()->eq($this->primaryKeyName, ':id'))
            ->setParameter(':id', $id);

        if ($this->siteField) {
            $qb->andWhere($qb->expr()->eq($this->siteField, ':site'))
               ->setParameter(':site', $this->getSiteId());
        }

        $r = $qb->execute()->fetch();

        if ($r) {
            $this->loadFromArray($r);
            return true;
        } else {
            $this->emptyRecord();
            return false;
        }
    }

    function loadFromArray($values)
    {
        if (!empty($values)) {
            $this->emptyRecord();

            if (__Config::get('glizy.dataAccess.serializationMode') == 'json') {
                $this->data = json_decode($values[self::DOCUMENT_TABLE_OBJECT]);
            }
            else {
                $data = unserialize($values[self::DOCUMENT_TABLE_OBJECT]);

                // TODO rimuovere quando la migrazine dei dati da serializzazione php a json è completata
                if (is_array($data)) {
                    foreach ($data as $k => $v) {
                        $this->data->$k = $v;
                    }
                }
                else  {
                    $this->data = $data;
                }
            }

            if (!is_object($this->data)) {
                $this->data = new StdClass;
            }

            foreach ($values as $k => $v) {
                if (strpos($k, self::DOCUMENT_BASE_PREFIX)===false) {
                    $this->virtualData->$k = $v;
                }
            }

            $this->setId($values[self::DOCUMENT_TABLE_ID]);
            if ($values[self::DOCUMENT_TABLE_TYPE]) $this->setType($values[self::DOCUMENT_TABLE_TYPE]);
        }
    }

    public function emptyRecord()
    {
        parent::emptyRecord();
        $this->setType($this->type);
    }

    protected function insert($values = NULL)
    {
        // sequenceName deve essere letto prima della insert
        // altrimenti può creare problemi con la insert del dettaglio
        $sequenceName = $this->getSequenceName();
        if (is_null($values)) {
            $values = get_object_vars($this->data);
        }

        $valArray = array();
        $valArrayIndex = array();

        // seleziona solo i valori non null o che non sia la chiave primaria dall'array dei valori
        foreach ($this->fields as $fieldName => $field) {
            if (!$field->isSystemField && !is_null($values[$fieldName]) && $values[$fieldName] != '') {
                if (!($field->index & $field::ONLY_INDEX)) {
                    $valArray[$fieldName] = $values[$fieldName];
                }
                $valArrayIndex[$fieldName] = $values[$fieldName];
            }
        }

        $document = array(self::DOCUMENT_TABLE_TYPE => $this->type);

        if (__Config::get('glizy.dataAccess.serializationMode') == 'json') {
            $document[self::DOCUMENT_TABLE_OBJECT] = json_encode($valArray);
        }
        else {
            $document[self::DOCUMENT_TABLE_OBJECT] = serialize($valArray);
        }

        if ($this->siteField) {
            $document[$this->siteField] = $this->getSiteId();
        }

        $r = $this->connection->insert($this->getDocumentTableName(), $document);

        if ($r != false) {
            $id = $this->connection->lastInsertId($sequenceName);
            $this->insertValuesIntoIndex($id, $valArrayIndex);
            $this->setId($id);
            $this->modifiedFields = array();
            return $this->getId();
        }
        else {
            return false;
        }
    }

    protected function insertValuesIntoIndex($id, $values)
    {
        foreach ($values as $fieldName => $value)
        {
            $field = $this->fields[$fieldName];

            if (!$field->virtual && !$field->isSystemField && $field->index != $field::NOT_INDEXED && isset(self::$typeMap[$field->type])) {
                $indexFieldType = $this->getIndexFieldType($fieldName);
                $tableName = $this->getDocumentIndexTablePrefix() . $indexFieldType . '_tbl';
                $fieldPrefix = self::DOCUMENT_INDEX_FIELD_PREFIX . $indexFieldType;

                $documentIndex = array(
                    $fieldPrefix.'_FK_simple_document_id' => $id,
                    $fieldPrefix.'_name' => $fieldName,
                    $fieldPrefix.'_value' => $value,
                );

                $types = array(
                    Type::INTEGER,
                    Type::STRING,
                    $field->type
                );

                $this->connection->insert($tableName, $documentIndex, $types);
            }
        }
    }

    protected function update($values=NULL)
    {
        if (is_null($values)) {
            // solo i valori dei campi modificati
            $updatedfields = array_intersect_key($this->fields, $this->modifiedFields);
            $values = get_object_vars($this->data);
        }
        else {
            $updatedfields = array_intersect_key($this->fields, $values);
        }

        $this->updateValuesIntoIndex($updatedfields, $values);

        // rimuove la chiave primaria dall'array dei valori
        $values = array_diff_key($values, array_flip(array($this->primaryKeyName)));

        // seleziona solo i valori non null o che non siano campi di sistema
        foreach ($updatedfields as $fieldName => $field) {
            if (!$field->isSystemField && !is_null($values[$fieldName])) {
                $valArray[$fieldName] = $values[$fieldName];
            }
        }

        if (__Config::get('glizy.dataAccess.serializationMode') == 'json') {
            $document[self::DOCUMENT_TABLE_OBJECT] = json_encode($values);
        }
        else {
            $document[self::DOCUMENT_TABLE_OBJECT] = serialize($values);
        }

        $identifier = array($this->primaryKeyName => $this->getId());

        $this->connection->update($this->getDocumentTableName(), $document, $identifier);

        return $this->getId();
    }

    protected function updateValuesIntoIndex($fields, $values)
    {
        // per ogni campo modificato si va a modificare la corrispondente tabella indice
        foreach ($fields as $fieldName => $field) {
            if (!$field->virtual && !$field->isSystemField && $field->index != $field::NOT_INDEXED && isset(self::$typeMap[$field->type])) {
                $indexFieldType = $this->getIndexFieldType($fieldName);
                $tableName = $this->getDocumentIndexTablePrefix() . $indexFieldType . '_tbl';
                $fieldPrefix = self::DOCUMENT_INDEX_FIELD_PREFIX . $indexFieldType;

                $indexIdentifier = array(
                                    $fieldPrefix.'_FK_simple_document_id' => $this->getId(),
                                    $fieldPrefix.'_name' => $fieldName
                                    );

                // cancella dall'indice il vecchio valore
                $this->connection->delete($tableName, $indexIdentifier);

                $value = $values[$fieldName];

                // inserisce nell'indice solo se il valore è non nullo
                if (!is_null($value) && $value !== '') {
                    $documentIndex = array_merge($indexIdentifier, array($fieldPrefix.'_value' => $value));

                    $types = array(
                        Type::INTEGER,
                        Type::STRING,
                        $field->type
                    );

                    $this->connection->insert($tableName, $documentIndex, $types);
                }
            }
        }
    }

    public function delete($id=NULL)
    {
        if (is_null($id)) {
            $id = $this->getId();
        }

        $indexTypes = array_unique(array_values(self::$typeMap));
        $indexTypes[] = 'fulltext';

        // cancella i campi nelle tabelle indice collegate alla tabella principale per l'id fornito
        foreach ($indexTypes as $indexType) {
            $tableName = $this->getDocumentIndexTablePrefix() . $indexType . '_tbl';
            $fieldPrefix = self::DOCUMENT_INDEX_FIELD_PREFIX . $indexType;
            $indexIdentifier = array($fieldPrefix.'_FK_simple_document_id' => $id);
            $this->connection->delete($tableName, $indexIdentifier);
        }

        $evt = array('type' => GLZ_EVT_AR_DELETE.'@'.$this->getClassName(), 'data' => $this);
        $this->dispatchEvent($evt);
        $this->emptyRecord();

        $identifier = array($this->primaryKeyName => $id);

        return $this->connection->delete($this->getDocumentTableName(), $identifier);
    }

    public function createRecordIterator() {
        return new org_glizy_dataAccessDoctrine_RecordIteratorSimpleDocument($this);
    }

    public function createQueryBuilder($addFrom=true, $tableAlias='t1') {
        $qb = $this->connection->createQueryBuilder();

        if ($addFrom) {
            $qb->from($this->getDocumentTableName(), $tableAlias);
        }

        return $qb;
    }

    private function loadSequenceName()
    {
        $this->sequenceNameLoaded = true;
        $sm = new org_glizy_dataAccessDoctrine_SchemaManager($this->connection);
        $sequenceName = $sm->getSequenceName($this->getDocumentTableName());
        $this->setSequenceName($sequenceName);
    }
}