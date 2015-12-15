<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

use Doctrine\DBAL\Types\Type;

class org_glizy_dataAccessDoctrine_ActiveRecordDocument extends org_glizy_dataAccessDoctrine_ActiveRecord
{
    const DOCUMENT_TABLE = 'documents_tbl';
    const DOCUMENT_TABLE_ALIAS = 'doc';
    const DOCUMENT_ID = 'document_id';
    const DOCUMENT_TYPE = 'document_type';
    const DOCUMENT_CREATION_DATE = 'document_creationDate';
    const DOCUMENT_FK_SITE_ID = 'document_FK_site_id';
    const DOCUMENT_DETAIL_TABLE = 'documents_detail_tbl';
    const DOCUMENT_DETAIL_TABLE_ALIAS = 'doc_detail';
    const DOCUMENT_DETAIL_TABLE_PUBLISHED_ALIAS = 'doc_detail_published';
    const DOCUMENT_DETAIL_TABLE_DRAFT_ALIAS = 'doc_detail_draft';
    const DOCUMENT_DETAIL_ID = 'document_detail_id';
    const DOCUMENT_DETAIL_FK_DOCUMENT = 'document_detail_FK_document_id';
    const DOCUMENT_DETAIL_FK_LANGUAGE = 'document_detail_FK_language_id';
    const DOCUMENT_DETAIL_FK_USER = 'document_detail_FK_user_id';
    const DOCUMENT_DETAIL_MODIFICATION_DATE = 'document_detail_modificationDate';
    const DOCUMENT_DETAIL_STATUS = 'document_detail_status';
    const DOCUMENT_DETAIL_TRANSLATED = 'document_detail_translated';
    const DOCUMENT_DETAIL_IS_VISIBLE = 'document_detail_isVisible';
    const DOCUMENT_DETAIL_NOTE = 'document_detail_note';
    const DOCUMENT_DETAIL_OBJECT = 'document_detail_object';
    const DOCUMENT_INDEX_TABLE_PREFIX = 'documents_index_';
    const DOCUMENT_INDEX_FIELD_PREFIX = 'document_index_';
    const DOCUMENT_BASE_PREFIX = 'document_';

    const STATUS_PUBLISHED = 'PUBLISHED';
    const STATUS_DRAFT = 'DRAFT';
    const STATUS_OLD = 'OLD';

    protected $detailPrimaryKeyName;
    protected $detailSequenceName;
    protected $status;
    protected $type;
    protected $isVisible = array();
    protected $hasPublishedVersion;
    protected $hasDraftVersion;

    protected static $typeMap = array(Type::INTEGER => 'int',
                                      Type::SMALLINT => 'int',
                                      Type::BIGINT => 'int',
                                      Type::STRING => 'text',
                                      Type::TEXT => 'text',
                                      Type::TARRAY => 'text',
                                      Type::DATE => 'date',
                                      Type::DATETIME => 'datetime',
                                      Type::TIME => 'time',
                                      org_glizy_dataAccessDoctrine_types_Types::ARRAY_ID => 'int');

    protected static $indexQueueEnabled = false;
    protected static $indexQueue = null;

    function __construct($connectionNumber=0)
    {
        parent::__construct($connectionNumber);

        $this->detailSequenceName = null;
        $this->status = null;

        foreach ($this->getLanguagesId() as $languageId) {
           $this->isVisible[$languageId] = 1;
        }

        $this->addField(new org_glizy_dataAccessDoctrine_SystemField(self::DOCUMENT_ID, Doctrine\DBAL\Types\Type::INTEGER, 10, true, null, null));
        $this->addField(new org_glizy_dataAccessDoctrine_SystemField(self::DOCUMENT_TYPE, Doctrine\DBAL\Types\Type::STRING, 255, false, null, 0, true, false, '', org_glizy_dataAccessDoctrine_DbField::NOT_INDEXED));
        $this->addField(new org_glizy_dataAccessDoctrine_SystemField(self::DOCUMENT_FK_SITE_ID, Doctrine\DBAL\Types\Type::INTEGER, 255, false, null, 0, true, false, '', org_glizy_dataAccessDoctrine_DbField::NOT_INDEXED));
        $this->addField(new org_glizy_dataAccessDoctrine_SystemField(self::DOCUMENT_CREATION_DATE, Doctrine\DBAL\Types\Type::DATETIME, 255, false, null, 0, true, false, '', org_glizy_dataAccessDoctrine_DbField::NOT_INDEXED));

        $this->addField(new org_glizy_dataAccessDoctrine_SystemField(self::DOCUMENT_DETAIL_ID, Doctrine\DBAL\Types\Type::INTEGER, 10, true, null, null));
        $this->addField(new org_glizy_dataAccessDoctrine_SystemField(self::DOCUMENT_DETAIL_FK_DOCUMENT, Doctrine\DBAL\Types\Type::INTEGER, 255, false, null, 0, true, false, '', org_glizy_dataAccessDoctrine_DbField::NOT_INDEXED));
        $this->addField(new org_glizy_dataAccessDoctrine_SystemField(self::DOCUMENT_DETAIL_FK_USER, Doctrine\DBAL\Types\Type::INTEGER, 255, false, null, 0, true, false, '', org_glizy_dataAccessDoctrine_DbField::NOT_INDEXED));
        $this->addField(new org_glizy_dataAccessDoctrine_SystemField(self::DOCUMENT_DETAIL_TRANSLATED, Doctrine\DBAL\Types\Type::INTEGER, 255, false, null, 0, true, false, '', org_glizy_dataAccessDoctrine_DbField::NOT_INDEXED));
        $this->addField(new org_glizy_dataAccessDoctrine_SystemField(self::DOCUMENT_DETAIL_MODIFICATION_DATE, Doctrine\DBAL\Types\Type::DATETIME, 255, false, null, 0, true, false, '', org_glizy_dataAccessDoctrine_DbField::NOT_INDEXED));
        $this->addField(new org_glizy_dataAccessDoctrine_SystemField(self::DOCUMENT_DETAIL_STATUS, Doctrine\DBAL\Types\Type::STRING, 255, false, null, 0, true, false, '', org_glizy_dataAccessDoctrine_DbField::NOT_INDEXED));
        $this->addField(new org_glizy_dataAccessDoctrine_SystemField(self::DOCUMENT_DETAIL_FK_LANGUAGE, Doctrine\DBAL\Types\Type::INTEGER, 10, false, null, 0, true, false, '', org_glizy_dataAccessDoctrine_DbField::NOT_INDEXED));
        $this->addField(new org_glizy_dataAccessDoctrine_SystemField(self::DOCUMENT_DETAIL_IS_VISIBLE, Doctrine\DBAL\Types\Type::INTEGER, 1, false, null, 1, true, false, '', org_glizy_dataAccessDoctrine_DbField::NOT_INDEXED));
        $this->addField(new org_glizy_dataAccessDoctrine_SystemField(self::DOCUMENT_DETAIL_OBJECT, Doctrine\DBAL\Types\Type::TEXT, 255, false, null, 1, true, false, '', org_glizy_dataAccessDoctrine_DbField::NOT_INDEXED));

        if (__Config::get('glizy.dataAccess.document.enableComment')) {
            $this->addField(new org_glizy_dataAccessDoctrine_SystemField(self::DOCUMENT_DETAIL_NOTE, Doctrine\DBAL\Types\Type::TEXT, 255, false, null, 1, true, false, '', org_glizy_dataAccessDoctrine_DbField::NOT_INDEXED));

        }

        if (__Config::get('MULTISITE_ENABLED')) {
            $this->siteField = self::DOCUMENT_FK_SITE_ID;
        }

        if (!self::$indexQueue) {
            self::$indexQueue = new org_glizy_dataAccessDoctrine_IndexQueue($this->connection);
        }
    }

    public function getBaseClassName()
    {
        return 'document';
    }

    public function addField(org_glizy_dataAccessDoctrine_DbField $field )
    {
        $this->fields[$field->name] = $field;

        if ($field->key) {
            if (!$this->primaryKeyName && $field->name == self::DOCUMENT_ID) {
                $this->primaryKeyName = $field->name;
            }
            else if (!$this->detailPrimaryKeyName && $field->name == self::DOCUMENT_DETAIL_ID) {
                $this->detailPrimaryKeyName = $field->name;
            } else {
                throw org_glizy_dataAccessDoctrine_ActiveRecordException::primaryKeyAlreadyDefined($this->tableName);
            }
        }
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

    public function getType()
    {
        return $this->{self::DOCUMENT_TYPE};
    }

    public function setType($type)
    {
        $this->type = $type;
        $this->{self::DOCUMENT_TYPE} = $type;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function getLanguage()
    {
        return $this->{self::DOCUMENT_DETAIL_FK_LANGUAGE};
    }

    public function setLanguage($languageId)
    {
        $this->{self::DOCUMENT_DETAIL_FK_LANGUAGE} = $languageId;
        $this->modifiedFields[self::DOCUMENT_DETAIL_FK_LANGUAGE] = true;
    }

    public function isTranslated()
    {
        return $this->{self::DOCUMENT_DETAIL_TRANSLATED};
    }

    public function isVisible()
    {
        return $this->{self::DOCUMENT_DETAIL_IS_VISIBLE};
    }

    public function setVisible($visible)
    {
        $this->isVisible[$this->getLanguageId()] = $visible;
        $this->modifiedFields[self::DOCUMENT_DETAIL_IS_VISIBLE] = true;
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

    public function setDetailSequenceName($detailSequenceName)
    {
        $this->detailSequenceName = $detailSequenceName;
    }

    public function getDocumentTableName()
    {
        return $this->tablePrefix.self::DOCUMENT_TABLE;
    }

    public function getDocumentDetailTableName()
    {
        return $this->tablePrefix.self::DOCUMENT_DETAIL_TABLE;
    }

    public function getDocumentTableIdName()
    {
        return self::DOCUMENT_ID;
    }

    public function getDocumentDetailTableIdName()
    {
        return self::DOCUMENT_DETAIL_ID;
    }

    public function getDocumentIndexTablePrefix()
    {
        return $this->tablePrefix.self::DOCUMENT_INDEX_TABLE_PREFIX;
    }

    public function getDocumentIndexFieldPrefix()
    {
        return self::DOCUMENT_INDEX_FIELD_PREFIX;
    }

    public function getTypeField()
    {
        return self::DOCUMENT_TYPE;
    }

    public function getLanguageField()
    {
        return self::DOCUMENT_DETAIL_FK_LANGUAGE;
    }

    public function getStatusField()
    {
        return self::DOCUMENT_DETAIL_STATUS;
    }

    function getIndexFieldType($fieldName)
    {
        $field = $this->fields[$fieldName];

        if (!$field) {
            throw org_glizy_dataAccessDoctrine_ActiveRecordException::undefinedField($this->_className, $fieldName);
        }

        if ($field->index & $field::FULLTEXT) {
            return 'fulltext';
        } else {
            $type = $this->fields[$fieldName]->type;

            if ($type==org_glizy_dataAccessDoctrine_types_Types::ARRAY_ID) {
                return self::$typeMap[$this->fields[$fieldName]->option[org_glizy_dataAccessDoctrine_types_Types::ARRAY_ID]['type']];
            }

            return self::$typeMap[$type];
        }
    }

    public function getLanguageId()
    {
        $editingLanguageId = org_glizy_ObjectValues::get('org.glizy', 'editingLanguageId');
        if (!is_null($editingLanguageId)) {
            return $editingLanguageId;
        } else {
            return org_glizy_ObjectValues::get('org.glizy', 'languageId');
        }
    }

    public function getLanguagesId()
    {
        // NOTE: non mi piace questa dipendenza sulla lingue disponibili
        $languagesId = org_glizy_ObjectValues::get('org.glizy', 'languagesId');
        return !is_null($languagesId) ? $languagesId : array($this->getLanguageId());
    }

    public function getUserId()
    {
        return org_glizy_ObjectValues::get('org.glizy', 'userId');
    }

    public function load($id, $status='PUBLISHED', $languageId=null)
    {
        if (empty($id)) {
            $this->emptyRecord();
            return false;
        }

        if ($languageId === null) {
            $languageId = $this->getLanguageId();
        }

        $qb = $this->createQueryBuilder(array('type' => 'PUBLISHED'));
        $expr = $qb->expr();

        $qb->select('*')
            ->where($expr->eq($this->primaryKeyName, ':id'))
            ->andWhere($expr->eq(self::DOCUMENT_DETAIL_FK_LANGUAGE, ':language'))
            ->setParameter(':id', $id)
            ->setParameter(':language', $languageId)
            ->setMaxResults(1);

        // restituisce prima PUBLISHED se c'è, altrimenti DRAFT grazie all'orderBy sullo status
        if ($status == 'PUBLISHED') {
            $or = $expr->orX();
            $or->add($expr->eq(self::DOCUMENT_DETAIL_STATUS, ':status1'))
               ->add($expr->eq(self::DOCUMENT_DETAIL_STATUS, ':status2'));

            $qb->andWhere($or)
               ->setParameter(':status1', self::STATUS_DRAFT)
               ->setParameter(':status2', self::STATUS_PUBLISHED)
               ->orderBy(self::DOCUMENT_DETAIL_STATUS, 'DESC');
        } else if ($status == 'DRAFT') {
            $qb->andWhere($expr->eq(self::DOCUMENT_DETAIL_STATUS, ':status1'))
               ->setParameter(':status1', self::STATUS_DRAFT);
        } else if ($status == 'LAST_MODIFIED') {
            $qb->orderBy(self::DOCUMENT_DETAIL_MODIFICATION_DATE, 'DESC');
        }

        if ($this->siteField) {
            $qb->andWhere($expr->eq($this->siteField, ':site'))
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

    public function hasPublishedVersion()
    {
        return $this->hasPublishedVersion;
    }

    public function hasDraftVersion()
    {
        return $this->hasDraftVersion;
    }

    public function loadFromArray($values, $useSet=false)
    {
        if (!empty($values)) {
            $this->emptyRecord();

            $documentObjVo = __ObjectFactory::createObject('org.glizy.dataAccessDoctrine.vo.DocumentObjectVO', $values);

            $this->hasPublishedVersion = $documentObjVo->hasPublishedVersion();
            $this->hasDraftVersion = $documentObjVo->hasDraftVersion();

            $detailObj = $documentObjVo->{self::DOCUMENT_DETAIL_OBJECT};

            if ($detailObj) {
                if (__Config::get('glizy.dataAccess.serializationMode') == 'json') {
                    $this->data = json_decode($detailObj);
                } else {
                    $data = unserialize($detailObj);

                    // TODO rimuovere quando la migrazione dei dati da serializzazione php a json è completata
                    if (is_array($data)) {
                        foreach ($data as $k => $v) {
                            $this->data->$k = $v;
                        }
                    } else  {
                        $this->data = $data;
                    }
                }
            }

            if (!is_object($this->data)) {
                $this->data = new StdClass;
            }

            foreach ($values as $k => $v) {
                if (strpos($k, self::DOCUMENT_BASE_PREFIX)===false) {
                    $this->virtualData->$k = $documentObjVo->$k;
                } else {
                    $this->data->$k = $documentObjVo->$k;
                }
            }

            if ($values[self::DOCUMENT_ID]) $this->setId($values[self::DOCUMENT_ID]);

            // NOTA: forse non è opportuno non permettere l'override del tipo
            if ($values[self::DOCUMENT_TYPE]) $this->setType($values[self::DOCUMENT_TYPE]);
            if ($documentObjVo->{self::DOCUMENT_DETAIL_ID}) $this->setDetailId($documentObjVo->{self::DOCUMENT_DETAIL_ID});
            if ( $documentObjVo->{self::DOCUMENT_DETAIL_STATUS}) $this->setStatus( $documentObjVo->{self::DOCUMENT_DETAIL_STATUS});
        }
    }

    public function emptyRecord()
    {
        parent::emptyRecord();
        $this->setType($this->type);
        $this->status = null;
    }

    // TODO
    // gestire senza recordIterator
    public function find($options=array())
    {
        $options = array_merge(get_object_vars($this->data), $options);
        $it = $this->createRecordIterator();

        foreach($options as $k => $v) {
            $it->where($k, $v);
        }

        if ($this->siteField && !isset($options[$this->siteField])) {
            $it->where($this->siteField, $this->getSiteId());
        }

        $ar = $it->first();

        if ($ar) {
            $this->loadFromArray($ar->getValuesAsArray());
            return true;
        } else {
            $this->emptyRecord();
            return false;
        }
    }

    public function save($values=null, $forceNew=false, $status=self::STATUS_DRAFT, $comment='')
    {
        if (!is_null($values)) {
            $this->loadFromArray($values, true);
        }

        if (__Config::get('glizy.dataAccess.validate')) {
            $this->validate();
        }

        if ( $this->isNew() || $forceNew )
        {
            $evt = array('type' => GLZ_EVT_AR_INSERT_PRE.'@'.$this->getClassName(), 'data' => $this);
            $this->dispatchEvent($evt);
            $result = $this->insert($values, $status, $comment);
            $evt = array('type' => GLZ_EVT_AR_INSERT.'@'.$this->getClassName(), 'data' => $this);
            $this->dispatchEvent($evt);
        }
        else
        {
            $evt = array('type' => GLZ_EVT_AR_UPDATE_PRE.'@'.$this->getClassName(), 'data' => $this);
            $this->dispatchEvent($evt);
            $result = $this->update($values, $comment);
            $evt = array('type' => GLZ_EVT_AR_UPDATE.'@'.$this->getClassName(), 'data' => $this);
            $this->dispatchEvent($evt);
        }

        return $result;
    }

    protected function insert($values = NULL, $status=self::STATUS_DRAFT, $comment)
    {
        // sequenceName deve essere letto prima della insert
        // altrimenti può creare problemi con la insert del dettaglio
        $sequenceName = $this->getSequenceName();

        if (is_null($values)) {
            $values = get_object_vars($this->data);
        }

        $valArray = array();
        $valArrayIndex = array();

        // seleziona solo i valori che non siano campi di sistema
        foreach ($values as $fieldName => $value) {
            $field = $this->fields[$fieldName];
            if ($field && !$field->isSystemField) {
                if (!($field->index & $field::ONLY_INDEX) && !is_null($value) && $value !== '') {
                    $valArray[$fieldName] = $value;
                }
                $valArrayIndex[$fieldName] = is_string($value) ? trim(strip_tags($value)) : $value;
            }
        }

        $document = array(
            self::DOCUMENT_TYPE => $this->getType(),
            self::DOCUMENT_CREATION_DATE => new org_glizy_types_DateTime()
        );

        if ($this->siteField && !isset($document[$this->siteField])) {
            $document[$this->siteField] = $this->getSiteId();
        }

        $r = $this->connection->insert($this->getDocumentTableName(), $document);

        if ($r != false) {
            $id = $this->connection->lastInsertId($sequenceName);

            $languages = $this->getLanguagesId();

            foreach ($languages as $languageId) {
                $detailId = $this->insertValues($id, $valArray, $status, $languageId, $comment);
                $this->insertValuesIntoIndex($detailId, $valArrayIndex);

                if ($languageId == $this->getLanguageId()) {
                    $this->setId($id);
                    $this->setDetailId($detailId);
                    $this->modifiedFields = array();
                }
            }
            return $this->getId();
        }
        else {
            return false;
        }
    }

    protected function insertValues($id, $values, $status = self::STATUS_DRAFT, $languageId, $comment)
    {
        $sequenceName = $this->getDetailSequenceName();
        $documentDetail = array(
            self::DOCUMENT_DETAIL_FK_DOCUMENT => $id,
            self::DOCUMENT_DETAIL_FK_LANGUAGE => $languageId,
            self::DOCUMENT_DETAIL_FK_USER => $this->getUserId(),
            self::DOCUMENT_DETAIL_MODIFICATION_DATE => new org_glizy_types_DateTime(),
            self::DOCUMENT_DETAIL_STATUS => $status,
            self::DOCUMENT_DETAIL_TRANSLATED => $languageId == $this->getLanguageId(),
            self::DOCUMENT_DETAIL_IS_VISIBLE => $this->isVisible[$languageId]
        );

        if (__Config::get('glizy.dataAccess.serializationMode') == 'json') {
            $documentDetail[self::DOCUMENT_DETAIL_OBJECT] = json_encode($values);
        }
        else {
            $documentDetail[self::DOCUMENT_DETAIL_OBJECT] = serialize($values);
        }

        $types = array(
            Type::INTEGER,
            Type::INTEGER,
            Type::INTEGER,
            Type::DATETIME,
            Type::STRING,
            Type::INTEGER,
            Type::TEXT
        );

        if (__Config::get('glizy.dataAccess.document.enableComment')) {
            $documentDetail[self::DOCUMENT_DETAIL_NOTE] = $comment;
            $types[] = Type::TEXT;
        }

        $this->setStatus($status);
        $this->connection->insert($this->getDocumentDetailTableName(), $documentDetail, $types);
        return $this->connection->lastInsertId($sequenceName);
    }

    protected function insertValuesIntoIndex($id, $values)
    {
        foreach ($values as $fieldName => $value) {
            $field = $this->fields[$fieldName];
            if (!$field->virtual && $field->index != $field::NOT_INDEXED && isset(self::$typeMap[$field->type])) {
                $indexFieldType = $this->getIndexFieldType($fieldName);
                $tableName = $this->getDocumentIndexTablePrefix() . $indexFieldType . '_tbl';
                $fieldPrefix = self::DOCUMENT_INDEX_FIELD_PREFIX . $indexFieldType;

                $documentIndex = array(
                    $fieldPrefix.'_FK_document_detail_id' => $id,
                    $fieldPrefix.'_name' => $fieldName
                );

                $types = array(
                    Type::INTEGER,
                    Type::STRING
                );

                // TODO
                // troncare value se l'indice è 'text' e se value è maggiore di 255

                // se $value è un array inserisce i singoli valori nell'indice
                if (is_array($value)) {
                    foreach ($value as $v) {
                        $v = $this->getValueToIndex($field->type, $v, $field->option);
                        if (!is_null($v)) {
                            $documentIndex[$fieldPrefix.'_value'] = $v;
                            $this->connection->insert($tableName, $documentIndex);
                        }
                    }
                } else {
                    $value = $this->getValueToIndex($field->type, $value, $field->option);

                    if ((!is_null($value) && $value !== '') || $field->type == Type::STRING) {
                        $documentIndex[$fieldPrefix.'_value'] = $value;
                        $types[] = $field->type;
                        if (!self::$indexQueueEnabled) {
                            $this->connection->insert($tableName, $documentIndex, $types);
                        } else {
                            $documentIndex[$fieldPrefix.'_name'] = "'".$documentIndex[$fieldPrefix.'_name']."'";
                            $documentIndex[$fieldPrefix.'_value'] = $this->connection->quote($documentIndex[$fieldPrefix.'_value'], $field->type);
                            self::$indexQueue->insert($tableName, $documentIndex, $types);
                        }
                    }
                }
            }
        }
    }

    public function reIndex($reloadContent=true)
    {
        list($documentDetailIds, $languages) =  $this->findDetailRecords($this->getId());
        foreach ($documentDetailIds as $documentDetailId => $data)  {
            if ($reloadContent) {
                $this->loadFromArray($data);
            }
            $fields = $this->fields;
            unset($fields['fulltext']);
            $values = get_object_vars($this->data);
            foreach ($fields as $fieldName => $field) {
                if (is_null($values[$fieldName])) {
                    $values[$fieldName] = '';
                }
            }
            $this->updateValuesIntoIndex($documentDetailId, $documentDetailId, $fields, $values);
        }
    }

    protected function update($values=NULL, $comment='')
    {

        if (is_null($values)) {
            // solo i valori dei campi modificati
            $updatedfields = array_intersect_key($this->fields, $this->modifiedFields);
            $values = get_object_vars($this->data);
        }
        else {
            $updatedfields = array_intersect_key($this->fields, $values);
        }

        $this->updateValuesIntoIndex($this->getDetailId(), $this->getDetailId(), $updatedfields, $values);

        $valArray = array();
        foreach ($values as $fieldName => $value) {
            $field = $this->fields[$fieldName];
            if ($field && !$field->isSystemField) {
                if (!($field->index & $field::ONLY_INDEX) && !is_null($value) && $value !== '') {
                    $valArray[$fieldName] = $value;
                }
            }
        }

        $this->updateValues($valArray, $comment);

        return $this->getId();
    }

    protected function updateValues($values, $comment)
    {
        $identifier = array(self::DOCUMENT_DETAIL_ID => $this->getDetailId());

        $documentDetail = array(
            self::DOCUMENT_DETAIL_TRANSLATED => true,
            self::DOCUMENT_DETAIL_MODIFICATION_DATE => new org_glizy_types_DateTime(),
            self::DOCUMENT_DETAIL_IS_VISIBLE => $this->isVisible[$this->getLanguageId()]
        );

        if (__Config::get('glizy.dataAccess.serializationMode') == 'json') {
            $documentDetail[self::DOCUMENT_DETAIL_OBJECT] = json_encode($values);
        } else {
            $documentDetail[self::DOCUMENT_DETAIL_OBJECT] = serialize($values);
        }

        $types = array(
            Type::INTEGER,
            Type::INTEGER,
            Type::DATETIME,
            Type::TEXT
        );

        if (__Config::get('glizy.dataAccess.document.enableComment')) {
            $documentDetail[self::DOCUMENT_DETAIL_NOTE] = $comment;
            $types[] = Type::TEXT;
        }

        return $this->connection->update($this->getDocumentDetailTableName(), $documentDetail, $identifier, $types);
    }

    protected function updateValuesIntoIndex($newDetailId, $oldDetailId, $fields, $values)
    {
        // per ogni campo modificato si va a modificare la corrispondente tabella indice
        foreach ($fields as $fieldName => $field) {
            if (!$field->virtual && !$field->isSystemField && $field->index != $field::NOT_INDEXED && isset(self::$typeMap[$field->type])) {
                $indexFieldType = $this->getIndexFieldType($fieldName);
                $tableName = $this->getDocumentIndexTablePrefix() . $indexFieldType . '_tbl';
                $fieldPrefix = self::DOCUMENT_INDEX_FIELD_PREFIX . $indexFieldType;

                $documentIndex = array(
                    $fieldPrefix.'_FK_document_detail_id' => $oldDetailId,
                    $fieldPrefix.'_name' => $fieldName
                );

                $types = array(
                    Type::INTEGER,
                    Type::STRING
                );

                // cancella dall'indice il vecchio valore
                $this->connection->delete($tableName, $documentIndex);

                $documentIndex[$fieldPrefix.'_FK_document_detail_id'] = $newDetailId;

                $value = $values[$fieldName];

                if (is_array($value)) {
                    foreach ($value as $v) {
                        $v = $this->getValueToIndex($field->type, $v, $field->option);
                        if (!is_null($v)) {
                            $documentIndex[$fieldPrefix.'_value'] = $v;
                            $this->connection->insert($tableName, $documentIndex);
                        }
                    }
                } else {
                    $value = $this->getValueToIndex($field->type, $value, $field->option);

                    if ((!is_null($value) && $value !== '') || $field->type == Type::STRING) {
                        $documentIndex[$fieldPrefix.'_value'] = $value;
                        $types[] = $field->type;
                        if (!self::$indexQueueEnabled) {
                            $this->connection->insert($tableName, $documentIndex, $types);
                        } else {
                            $documentIndex[$fieldPrefix.'_name'] = "'".$documentIndex[$fieldPrefix.'_name']."'";
                            $documentIndex[$fieldPrefix.'_value'] = $this->connection->quote($documentIndex[$fieldPrefix.'_value'], $field->type);
                            self::$indexQueue->insert($tableName, $documentIndex, $types);
                        }
                    }
                }
            }
        }
    }

    protected function insertDetailOnly($values = NULL, $currentStatus, $newStatus = self::STATUS_DRAFT, $comment='')
    {
        $evt = array('type' => GLZ_EVT_AR_UPDATE_PRE.'@'.$this->getClassName(), 'data' => $this);
        $this->dispatchEvent($evt);

        if (is_null($values)) {
            $values = get_object_vars($this->data);
        }

        $valArray = array();
        $valArrayIndex = array();

        // seleziona solo i valori non null o che non siano campi di sistema
        foreach ($this->fields as $fieldName => $field) {
            if (!$field->isSystemField && !is_null($values[$fieldName]) && $values[$fieldName] !== '') {
                if (!($field->index & $field::ONLY_INDEX)) {
                    $valArray[$fieldName] = $values[$fieldName];
                }
                $value = $values[$fieldName];
                $valArrayIndex[$fieldName] = is_string($value) ? trim(strip_tags($value)) : $value;
            }
        }

        $id = $this->getId();

        list($documentDetailIds, $languages) = $this->findDetailRecords($id, $currentStatus);

        // verifica se esiste un record di dettaglio per la lingua corrente
        if ($languages[$this->getLanguageId()]) {
            foreach ($documentDetailIds as $documentDetailId => $data) {
                $languageId = $data[self::DOCUMENT_DETAIL_FK_LANGUAGE];

                // non modifica i record nelle altre lingue che già sono stati tradotti
                if ($languageId != $this->getLanguageId() && $data[self::DOCUMENT_DETAIL_TRANSLATED]) {
                    continue;
                }

                $updateOld = false;

                if ($newStatus === self::STATUS_PUBLISHED) {
                    // setta a OLD tutti i record precedenti
                    $identifier = array(
                        self::DOCUMENT_DETAIL_FK_DOCUMENT => $id,
                        self::DOCUMENT_DETAIL_FK_LANGUAGE => $languageId
                    );
                    $updateOld = true;
                } else if ($currentStatus !== self::STATUS_PUBLISHED) {
                    // setta a OLD lo stato della versione precedente del record da modificare
                    $identifier = array(self::DOCUMENT_DETAIL_ID => $documentDetailId);
                    $updateOld = true;
                }

                if ($updateOld) {
                    $oldStatus = array(self::DOCUMENT_DETAIL_STATUS => self::STATUS_OLD);
                    $this->connection->update($this->getDocumentDetailTableName(), $oldStatus, $identifier);
                }

                $newDetailId = $this->insertValues($id, $valArray, $newStatus, $languageId, $comment);
                $this->insertValuesIntoIndex($newDetailId, $valArrayIndex);

                if ($languageId == $this->getLanguageId()) {
                    $this->setId($id);
                    $this->setDetailId($newDetailId);
                    $this->modifiedFields = array();
                    $returnDetailId = $newDetailId;
                }
            }
        } else {
            // si sta inserendo un record di dettaglio per una nuova lingua
            $languageId = $this->{self::DOCUMENT_DETAIL_FK_LANGUAGE};
            $newDetailId = $this->insertValues($id, $valArray, $newStatus, $languageId, $comment);
            $this->insertValuesIntoIndex($newDetailId, $valArrayIndex);
            $this->setId($id);
            $this->setDetailId($newDetailId);
            $this->modifiedFields = array();
            $returnDetailId = $newDetailId;
        }

        $evt = array('type' => GLZ_EVT_AR_UPDATE.'@'.$this->getClassName(), 'data' => $this);
        $this->dispatchEvent($evt);

        return $returnDetailId;
    }

    private function findDetailRecords($id, $status = null)
    {
        $qb = $this->createQueryBuilder(array('type' => 'PUBLISHED'));

        // cerca gli id dei record nella tabella dettaglio collegati alla tabella principale del documento da cancellare
        $qb->select('*')
            ->where($qb->expr()->eq(self::DOCUMENT_ID, ':id'))
            ->setParameter(':id', $id);

        if ($status != null) {
            $qb->andWhere($qb->expr()->eq(self::DOCUMENT_DETAIL_STATUS, ':status'))
               ->setParameter(':status', $status);
        }

        if ($this->siteField) {
            $qb->andWhere($qb->expr()->eq($this->siteField, ':site'))
               ->setParameter(':site', $this->getSiteId());
        }

        $statement = $qb->execute();

        $documentDetailIds = array();
        $languages = array();

        while ($data = $statement->fetch())  {
            $documentDetailId = $data[self::DOCUMENT_DETAIL_ID];
            $documentDetailIds[$documentDetailId] = $data;
            $languages[$data[self::DOCUMENT_DETAIL_FK_LANGUAGE]] = true;
        }

        return array($documentDetailIds, $languages);
    }

    private function getValueToIndex($type, $value, $fieldOption=null) {
        if ($type == org_glizy_dataAccessDoctrine_types_Types::ARRAY_ID && $fieldOption && is_object($value)) {
            $field = $fieldOption[org_glizy_dataAccessDoctrine_types_Types::ARRAY_ID]['field'];
            if (property_exists($value, $field)) {
                return $value->{$field};
            } else {
               return null;
            }
        }
        return $value;
    }

    // se il record è nuovo viene salvato con lo stato DRAFT
    // in caso di update fa diventare il record corrente a OLD se non è PUBLISHED
    // e inserisce un nuovo record con lo stato DRAFT
    public function saveHistory($values=NULL, $forceNew=false, $comment='')
    {
        if (__Config::get('glizy.dataAccess.validate')) {
            $this->validate($values);
        }

        if (!is_null($values)) {
            parent::loadFromArray($values);
        }

        if ( $this->isNew() || $forceNew ) {
            $result = $this->insert($values, self::STATUS_DRAFT, $comment);
        } else {
            if (!empty($this->modifiedFields) || !is_null($values)) {
                $result = $this->insertDetailOnly($values, $this->getStatus(), self::STATUS_DRAFT, $comment);
            }
        }

        return $this->getId();
    }

    // se il record è nuovo viene salvato con lo stato PUBLISHED
    // in caso di update fa diventare i record precedenti a OLD
    // e inserisce un nuovo record con lo stato PUBLISHED
    public function publish($values = null, $comment='', $forceNew=false)
    {
        if (__Config::get('glizy.dataAccess.validate')) {
            $this->validate($values);
        }

        // se il record non è stato salvato, si salva prima di fare publish
        if ($this->getStatus() === self::STATUS_DRAFT || !empty($this->modifiedFields) || !is_null($values)) {
            // se il record corrente è già self::STATUS_PUBLISHED si crea un nuovo record di dettaglio
            if (!$forceNew && !$this->isNew()) {
                $this->insertDetailOnly($values, $this->getStatus(), self::STATUS_PUBLISHED, $comment);
            } else {
                $this->save($values, $forceNew, self::STATUS_PUBLISHED, $comment);
            }
        }
        $this->setStatus(self::STATUS_PUBLISHED);
        return $this->getId();
    }

    public function delete($id=NULL)
    {
        if (is_null($id)) {
            $id = $this->getId();
        } else {
            $this->setId($id);
        }

        $indexTypes = array_unique(array_values(self::$typeMap));
        $indexTypes[] = 'fulltext';

        $documentDetailIds = $this->findDetailRecords($id);

        foreach ($documentDetailIds as $documentDetailId => $data)  {
            foreach ($indexTypes as $indexType) {
                $tableName = $this->getDocumentIndexTablePrefix() . $indexType . '_tbl';
                $fieldPrefix = self::DOCUMENT_INDEX_FIELD_PREFIX . $indexType;
                $indexIdentifier = array($fieldPrefix.'_FK_document_detail_id' => $documentDetailId);
                $this->connection->delete($tableName, $indexIdentifier);
            }
        }

        // cancella i record nella tabella dettaglio collegati alla tabella principale
        $detailId = array(self::DOCUMENT_DETAIL_FK_DOCUMENT => $id);
        $this->connection->delete($this->getDocumentDetailTableName(), $detailId);

        // cancella il record nella tabella principale
        $identifier = array(self::DOCUMENT_ID => $id);
        $r = $this->connection->delete($this->getDocumentTableName(), $identifier);

        $evt = array('type' => GLZ_EVT_AR_DELETE.'@'.$this->getClassName(), 'data' => $this);
        $this->dispatchEvent($evt);
        $this->emptyRecord();
        return $r;
    }

    public function createRecordIterator() {
        return new org_glizy_dataAccessDoctrine_RecordIteratorDocument($this);
    }

    protected function queryBuilderJoinDetail($qb, $tableAlias, $tableDetailAlias, $status, $paramName)
    {
        $and = $qb->expr()->andX(
            $qb->expr()->eq($tableAlias.'.'.self::DOCUMENT_ID, $tableDetailAlias.'.'.self::DOCUMENT_DETAIL_FK_DOCUMENT),
            $qb->expr()->eq($tableDetailAlias.'.'.self::DOCUMENT_DETAIL_STATUS, "'".$status."'"),
            $qb->expr()->eq($tableDetailAlias.'.'.self::DOCUMENT_DETAIL_FK_LANGUAGE, $paramName)
        );

        $qb->from($this->getDocumentTableName(), $tableAlias)
           ->leftJoin($tableAlias, $this->getDocumentDetailTableName(), $tableDetailAlias, $and);

    }

    public function createQueryBuilder($options)
    {
        $qb = $this->connection->createQueryBuilder();

        if ($options['type'] == 'PUBLISHED') {
            $tableAlias = self::DOCUMENT_TABLE_ALIAS;
            $tableDetailAlias = self::DOCUMENT_DETAIL_TABLE_ALIAS;

            $qb->from($this->getDocumentTableName(), $tableAlias)
               ->join($tableAlias, $this->getDocumentDetailTableName(), $tableDetailAlias,
                      $qb->expr()->eq($tableAlias.'.'.self::DOCUMENT_ID, $tableDetailAlias.'.'.self::DOCUMENT_DETAIL_FK_DOCUMENT));
        } else if ($options['type'] == 'PUBLISHED_DRAFT') {
            $tableAlias = $options['tableAlias'];
            $tableDetailPublishedAlias = $options['tableDetailPublishedAlias'];
            $tableDetailDraftAlias = $options['tableDetailDraftAlias'];

            $this->queryBuilderJoinDetail($qb, $tableAlias, $tableDetailPublishedAlias, self::STATUS_PUBLISHED, ':language');
            $this->queryBuilderJoinDetail($qb, $tableAlias, $tableDetailDraftAlias, self::STATUS_DRAFT, ':language');
            $qb->setParameter(':language', $this->getLanguageId());

            $languageProxy = __ObjectFactory::createObject('org.glizycms.languages.models.proxy.LanguagesProxy');
            $defaultLanguageId = $languageProxy->getDefaultLanguageId();

            if ($defaultLanguageId != $this->getLanguageId()) {
                $this->queryBuilderJoinDetail($qb, $tableAlias, $tableDetailPublishedAlias.'_defaultLanguage', self::STATUS_PUBLISHED, ':defaultLanguage');
                $this->queryBuilderJoinDetail($qb, $tableAlias, $tableDetailDraftAlias.'_defaultLanguage', self::STATUS_DRAFT, ':defaultLanguage');
                $qb->setParameter(':defaultLanguage', $defaultLanguageId);
            }
        }

        return $qb;
    }

    public static function enableIndexQueue()
    {
        self::$indexQueueEnabled = true;
    }

    public static function executeIndexQueue()
    {
        self::$indexQueue->execute();
    }


    private function loadSequenceName()
    {
        $this->sequenceNameLoaded = true;
        $sm = new org_glizy_dataAccessDoctrine_SchemaManager($this->connection);
        $sequenceName = $sm->getSequenceName($this->getDocumentTableName());
        $this->setSequenceName($sequenceName);

        $sm = new org_glizy_dataAccessDoctrine_SchemaManager($this->connection);
        $detailSequenceName = $sm->getSequenceName($this->getDocumentDetailTableName());
        $this->setDetailSequenceName($detailSequenceName);
    }
}