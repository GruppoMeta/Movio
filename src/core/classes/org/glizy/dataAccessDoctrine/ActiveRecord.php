<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

use Doctrine\DBAL\Types\Type;

class org_glizy_dataAccessDoctrine_ActiveRecord extends GlizyObject
{
    protected $connection;
    protected $tableName;
    protected $tablePrefix;
    protected $sequenceName = null;
    protected $sequenceNameLoaded = false;
    protected $primaryKeyName;
    protected $fields = array();
    protected $modifiedFields = array();
    protected $data;
    protected $virtualData;
    protected $siteField = null;
    protected $relations = array();
    protected $processRelations = false;
    protected $relationBuilded = false;
    protected $driverName;
    protected $baseclassName;

    function __construct($connectionNumber=0)
    {
        $this->connection = org_glizy_dataAccessDoctrine_DataAccess::getConnection($connectionNumber);
        $this->data = new StdClass();
        $this->virtualData = new StdClass();
        $this->driverName = __Config::get( 'DB_TYPE'.($connectionNumber == 0 ? '' : '#'.$connectionNumber) );
    }

    public function getConnection()
    {
        return $this->connection;
    }

    public function getDriverName()
    {
        return $this->driverName;
    }

    public function getBaseClassName()
    {
        return 'activerecord';
    }

    public function getTableName()
    {
        return $this->tableName;
    }

    public function getTableNameWithoutPrefix()
    {
        return substr( $this->getTableName(), strlen( $this->tablePrefix ) );
    }

    public function getTablePrefix()
    {
        return $this->tablePrefix;
    }


    public function setTableName($tableName, $prefix="")
    {
        $this->tablePrefix = $prefix;
        $this->tableName = $this->tablePrefix.$tableName;
    }

    public function setSequenceName($sequenceName)
    {
        $this->sequenceName = $sequenceName;
    }

    public function getSequenceName()
    {
        if (!$this->sequenceNameLoaded) {
            $this->loadSequenceName();
        }

        return $this->sequenceName;
    }

    public function getProcessRelations()
    {
        return $this->processRelations;
    }

    public function setProcessRelations($value)
    {
        $this->processRelations = $value;
    }

    public function addField(org_glizy_dataAccessDoctrine_DbField $field )
    {
        $this->fields[$field->name] = $field;

        if ($field->key) {
            if (!$this->primaryKeyName) {
                $this->primaryKeyName = $field->name;
            } else {
                throw org_glizy_dataAccessDoctrine_ActiveRecordException::primaryKeyAlreadyDefined($this->tableName);
            }
        }
    }

    // serve per cambiare un parametro di un campo a runtime
    public function setFieldParam($fieldName, $param, $value)
    {
        $field = $this->getField($fieldName);
        $field->$param = $value;
    }

    public function getPrimaryKeyName()
    {
        return $this->primaryKeyName;
    }

    public function getField($fieldName)
    {
        return $this->fields[$fieldName];
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function getFieldType($fieldName)
    {
        return isset($this->fields[$fieldName]) ? $this->fields[$fieldName]->type : '';
    }

    public function getSiteId()
    {
        return org_glizy_ObjectValues::get('org.glizy', 'siteId');
    }

    public function setSiteField($fieldName)
    {
        if ($this->fieldExists($fieldName)) {
        	$this->siteField = $fieldName;
    	}
    }

    public function getSiteField()
    {
        return $this->siteField;
    }

    public function resetSiteField()
    {
        $this->siteField = null;
    }

    public function load($id)
    {
        if (empty($id)) {
            $this->emptyRecord();
            return false;
        }

        if (is_null($this->primaryKeyName)) {
            throw org_glizy_dataAccessDoctrine_ActiveRecordException::primaryKeyNotDefined($this->tableName);
        }

        $qb = $this->createQueryBuilder()
                   ->select('*')
                   ->where($this->primaryKeyName.' = :id')
                   ->setParameter(':id', $id);

        if ($this->siteField) {
            $qb->andWhere($qb->expr()->eq($this->siteField, ':site'))
               ->setParameter(':site', $this->getSiteId());
        }

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

    function loadFromArray($values, $useSet=false)
    {
        if (!empty($values)) {
            $this->emptyRecord();

            if ($useSet) {
                foreach ($values as $k => $v) {
                    $this->$k = $v;
                }
            }
            else {
                foreach ($values as $k => $v) {
                    if (property_exists($this, $k)) {
                        $this->$k = $v;
                    }
                    $this->data->$k = $v;
                }
            }

            foreach ($this->relations as $k=>$v)
            {
                if (array_key_exists($k, $values))
                {
                    $this->$k = $values[$k];
                }
            }
        }
    }

    public function loadFromQuery($name, $params)
    {
        $it = $this->createRecordIterator();
        $newAr = $it->load($name, $params)->first();
        if ( $newAr ) {
            $this->loadFromArray($newAr->getRawData());
        }

        return $newAr ? $this : false;
    }

    /**
     * @param  array  $values
     * @param  boolean $isNew
     * @return boolean
     */
    public function validate($values = null, $isNew=false)
    {
        $values = $this->collectValidateFields($values, $isNew);
        $validationErrors = array();

        foreach ($values as $fieldName => $value) {
            $field = $this->fields[$fieldName];

            if (is_null($field->validator) || $field->key) {
                continue;
            }

            $validationResult = $field->validator->validate($field->description, $value, $field->defaultValue);

            if (is_string($validationResult)) {
                $validationErrors[] = $validationResult;
            } else if (is_array($validationResult)) {
                $validationErrors = array_merge($validationErrors, $validationResult);
            }
        }

        if (!empty($validationErrors)) {
            throw new org_glizy_validators_ValidationException($validationErrors);
        }

        return true;
    }

    public function emptyRecord()
    {
        $this->data = new StdClass();
        // $this->virtualData = new StdClass();
        $this->modifiedFields = array();
    }

    public function addRelation($options)
    {
        assert(isset($options['name']));

        if ( empty( $options['objectName'] ) )
        {
            $options['objectName'] = $this->getTableNameWithoutPrefix().'#'.$options['name'];
        }
        $this->relations[$options['name']] = $options;
        $this->{$options['bindTo']} = null;
    }



    protected function buildAllRelations($build = true)
    {
        if ($this->processRelations && !$this->relationBuilded)
        {
            $this->relationBuilded = true;

            // risolve le relazioni
            foreach ($this->relations as $k => $v)
            {
                $relation = org_glizy_dataAccessDoctrine_RelationFactory::createRelation($this, $v);
                if ( $build )  {
                    $relation->build();
                }
                $this->$k = $relation;
            }
        }
    }

    protected function saveAllRelations($preSave=true)
    {
        // TODO
        // quando si fa l'update anche delle relazioni
        // c'è da controllare che non si verifichino errori
        // in questo caso c'è da segnalarlo
        foreach ($this->relations as $k => $v) {
            if (is_object($this->$k)) {
                if ($preSave==true) {
                    $this->$k->preSave();
                } else {
                    $this->$k->postSave();
                }
            }
        }
    }

    protected function deleteAllRelations()
    {
        foreach ($this->relations as $k => $v) {
            if (is_object($this->$k)) {
                $this->$k->delete();
            }
        }

        $this->relationBuilded = false;
    }

    /**
     * @param  array  $options
     * @return boolean
     *
     * @throws org_glizy_dataAccessDoctrine_exceptions_DataAccessException|\Doctrine\DBAL\DBALException
     */
    public function find($options=array()) {
        $options = array_merge(get_object_vars($this->data), $options);
        $qb = $this->createQueryBuilder()
            ->select('*');

        $conditionNumber = 0;
        foreach($options as $k=>$v) {
            if (is_null($v)) continue;
            if (!isset($this->fields[$k])) {
                throw org_glizy_dataAccessDoctrine_exceptions_DataAccessException::unknownColumn($k, $this->getTableName());
            }
            $valueParam = ":value".$conditionNumber++;
            $qb->andWhere($qb->expr()->eq($k, $valueParam));
            $qb->setParameter($valueParam, $this->convertIfDateType($k, $v));
        }

        if ($this->siteField && !isset($options[$this->siteField])) {
            $qb->andWhere($qb->expr()->eq($this->siteField, ':site'))
               ->setParameter(':site', $this->getSiteId());
        }

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

    public function save($values = null, $forceNew = false)
    {
        if (!is_null($values)) {
            $this->loadFromArray($values, true);
        }

        if ($this->processRelations) {
            $this->buildAllRelations();
            $this->saveAllRelations(true);
        }

        if ( $this->isNew() || $forceNew )
        {
            if (__Config::get('glizy.dataAccess.validate')) {
                $this->validate(null, true);
            }
            $evt = array('type' => GLZ_EVT_AR_INSERT_PRE.'@'.$this->getClassName(), 'data' => $this);
            $this->dispatchEvent($evt);
            $result = $this->insert($values);
            $evt = array('type' => GLZ_EVT_AR_INSERT.'@'.$this->getClassName(), 'data' => $this);
            $this->dispatchEvent($evt);
        }
        else
        {
            if (__Config::get('glizy.dataAccess.validate')) {
                $this->validate(null);
            }
            $evt = array('type' => GLZ_EVT_AR_UPDATE_PRE.'@'.$this->getClassName(), 'data' => $this);
            $this->dispatchEvent($evt);
            $result = $this->update($values);
            $evt = array('type' => GLZ_EVT_AR_UPDATE.'@'.$this->getClassName(), 'data' => $this);
            $this->dispatchEvent($evt);
        }

        if ($this->processRelations)  {
            $this->saveAllRelations(false);
        }

        return $result;
    }

    protected function insert($values=NULL)
    {
        $sequenceName = $this->getSequenceName();
        if (is_null($values)) {
            $values = get_object_vars($this->data);
        }

        $insertValues = array();
        $types = array();

        // filtra i campi virtuali e la chiave primaria
        foreach ($values as $fieldName => $value) {
            if (isset($this->fields[$fieldName])) {
	            $field = $this->fields[$fieldName];
	            if (!$field->virtual && !$field->key) {
	                $insertValues[$fieldName] = $value;
	                $types[] = $field->type;
	            }
	        }
        }

        if ($this->siteField && !isset($values[$this->siteField])) {
            $insertValues[$this->siteField] = $this->getSiteId();
            $types[] = $this->fields[$this->siteField]->type;
        }

        if (!empty($insertValues)) {
            $r = $this->connection->insert($this->tableName, $insertValues, $types);
        }

        if ($r != false) {
            $this->modifiedFields = array();
            $this->setId($this->connection->lastInsertId($sequenceName));
            return $this->getId();
        }
        else {
            return false;
        }
    }

    protected function update($values=NULL)
    {
        $identifier = array($this->primaryKeyName => $this->getId());

        if (is_null($values)) {
            $values = array_intersect_key(get_object_vars($this->data), $this->modifiedFields);
        }

        $updateValues = array();
        $types = array();

        foreach ($values as $fieldName => $value) {
            if (isset($this->fields[$fieldName])) {
	            $field = $this->fields[$fieldName];
	            if (!$field->virtual && isset($this->modifiedFields[$fieldName])) {
	                $updateValues[$fieldName] = $value;
	                $types[] = $field->type;
	            }
	        }
        }

        if (!empty($updateValues)) {
           $this->connection->update($this->tableName, $updateValues, $identifier, $types);
        }

        return $this->getId();
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

        return $this->connection->delete($this->tableName, $identifier);
    }

    public function __call($name, $arguments)
    {
        return $this->{$name};

    }

    public function __get($name)
    {
        if (property_exists($this->data, $name)) {
            $value = $this->data->$name;
            return array_key_exists($name, $this->fields) ? $this->fields[$name]->format($value, $this->connection) : $value;
        }
        else if (property_exists($this->virtualData, $name)) {
            return $this->virtualData->$name;
        }
        else if (array_key_exists($name, $this->fields)) {
            return $this->fields[$name]->defaultValue;
        }

        throw org_glizy_dataAccessDoctrine_ActiveRecordException::getFailed($this->tableName, $name);
    }

    public function convertIfDateType($fieldName, $value)
    {
        if (!isset($this->fields[$fieldName])) {
            return $value;
        }
        $field = $this->fields[$fieldName];

        if ($field->type == Type::DATE || $field->type == Type::DATETIME) {
            return glz_localeDate2ISO($value);
        } else {
            return $value;
        }
    }

    public function __set($name, $value)
    {
        if (array_key_exists($name, $this->fields)) {
            $field = $this->fields[$name];
            // La condizione verifica che il campo è stato modificato, non è un campo di sistema
            if ($this->$name !== $value && !$field->isSystemField) {
                $this->modifiedFields[$name] = true;
            }
            $this->data->$name = $this->convertIfDateType($name, $value);
        }
        else {
            $this->virtualData->$name = $value;
        }
    }

    public function forceModified($name)
    {
         $this->modifiedFields[$name] = true;
    }

    public function getValues($getRelationValues=false, $getVirtualField=true, $encode=false, $systemFields=true)
    {
        $result = new StdClass;
        foreach ($this->data as $name => $value) {
            if ($systemFields == false && $this->fields[$name]->isSystemField) {
                continue;
            }
            $result->{$name} = array_key_exists($name, $this->fields) ? $this->fields[$name]->format($value, $this->connection) : $value;
        }

        foreach ($this->virtualData as $name => $value) {
            $result->{$name} = $value;
        }

        if ($getRelationValues) {
            $this->buildAllRelations();
            foreach ($this->relations as $k => $v) {
                if (!is_object($this->$k)) {
                    $result->$k = $this->$k;
                } else {
                    if ( method_exists( $this->$k, 'collectFieldsValues' ) ) {
                        $result->$k = $this->$k->collectFieldsValues( $getVirtualField, $encode );
                    } else {
                        $result->$k = null;
                    }
                }
            }
        }
        return $result;
    }

    public function getRawData()
    {
        return $this->data;
    }

    // restituisce anche i campi con valore null
    public function getValuesForced($getRelationValues=false, $getVirtualField=true, $encode=false, $systemFields=true)
    {
        $result = $this->getValues($getRelationValues, $getVirtualField, $encode, $systemFields);
        foreach($this->fields as $k=>$v) {
            if (!property_exists($result, $k)) {
                $result->$k = $v->defaultValue;
            }
        }
        return $result;
    }

    public function getValuesAsArray($getRelationValues=false, $getVirtualField=true, $encode=false, $systemFields=true)
    {
        $result = $this->getValues($getRelationValues, $getVirtualField, $encode, $systemFields);
        return get_object_vars($result);
    }

    function getFieldValue($name, $raw=false)
    {
		$this->buildAllRelations();
		return property_exists($this->data, $name) || property_exists($this->virtualData, $name) ? ($raw ? $this->data->$name : $this->$name) : '';
	}

    function getFieldValueByRegexp($name, $raw=false)
    {
		$this->buildAllRelations();

		foreach ($this->data as $k => $value) {
			if (strpos( $k, $name) !== false) {
				return $raw ? $this->data->$k : $this->$k;
			}
		}

		return '';
	}

    public function getId()
    {
        if (is_null($this->primaryKeyName)) {
            throw org_glizy_dataAccessDoctrine_ActiveRecordException::primaryKeyNotDefined($this->tableName);
        }
        $primarykey = $this->primaryKeyName;
        return $this->$primarykey;
    }

    public function setId($value)
    {
        $primarykey = $this->primaryKeyName;
        $this->$primarykey = $value;
    }

    public function isNew()
    {
        return is_null( $this->{$this->primaryKeyName} );
    }

    public function fieldExists($name)
    {
        return array_key_exists($name, $this->fields);
    }

    public function keyInDataExists($name) {
        return property_exists($this->data, $name);
    }

    public function isModified($name) {
        return $this->modifiedFields[$name] == true;
    }

    /**
     * @return org_glizy_dataAccessDoctrine_RecordIterator
     */
    public function createRecordIterator() {
        return new org_glizy_dataAccessDoctrine_RecordIterator($this);
    }

    /**
     * @param bool|true $addFrom
     * @param string    $tableAlias
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function createQueryBuilder($addFrom=true, $tableAlias='t1')
    {
        $qb = $this->connection->createQueryBuilder();

        if ($addFrom) {
            $qb->from($this->tableName, $tableAlias);
        }

        return $qb;
    }

    public function dump()
    {
        var_dump($this->getValuesAsArray());
    }

    private function loadSequenceName()
    {
        static $sequenceName;
        static $sequenceNameLoaded = false;
        if (!$sequenceNameLoaded) {
            $sequenceNameLoaded = true;
            $sm = new org_glizy_dataAccessDoctrine_SchemaManager($this->connection);
            $sequenceName = $sm->getSequenceName($this->getTableName());
        }
        $this->sequenceNameLoaded = true;
        $this->setSequenceName($sequenceName);
    }

    /**
     * @param  array  $values
     * @param  boolean $isNew
     * @return boolean
     */
    protected function collectValidateFields($values=null, $isNew=false)
    {
        if (is_null($values)) {
            $values = array();
            foreach ($this->fields as $fieldName => $field) {
                if ($field->isSystemField || $fieldName==$this->siteField) continue;
                $values[$fieldName] = $this->$fieldName;
            }

            if (!$isNew) {
                $values = array_intersect_key(get_object_vars($this->data), $this->modifiedFields);
            }
        }

        return $values;
    }
}