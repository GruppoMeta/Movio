<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */



class org_glizy_dataAccess_ActiveRecord extends GlizyObject
{
	var $_conn 				= NULL;
	var $_connNumber 		= 0;
	var $_tableName 		= '';
	var $_tablePrimaryKey 	= NULL;
	var $_fieldsList 		= array();
	var $_sql				= array();
	var $_querySql			= array();
	var $_error				= false;
	var $_relations			= array();
	var $_processRelations	= false;
	var $_relationBuilded	= false;
	var $_tablePrefix		= '';
	var $_defaultQuery 		= 'SELECT';
	var $_siteIdField		= NULL;
	var $_aclField			= NULL;
	var $_aclCategories		= NULL;
	var $lastSql			= '';
	var $enableLog = false;
	var $queue = NULL;

	function __construct()
	{
		$this->_conn = &org_glizy_dataAccess_DataAccess::getConnection($this->_connNumber);
		$application = org_glizy_ObjectValues::get('org.glizy', 'application');
		$this->enableLog = !is_null( $application->_logObj );
		//$this->_conn->debug = true;
	}

	function setDebugMode($value)
	{
		$this->_conn->debug = $value;
	}

	function getTableName()
	{
		return $this->_tableName;
	}

	function getTableNameWithoutPrefix()
	{
		return substr( $this->getTableName(), strlen( $this->_tablePrefix ) );
	}


	function setTableName($theName)
	{
		$this->_tableName = $this->_tablePrefix.$theName;
	}

	function setTablePrefix($prefix="")
	{
		$this->_tablePrefix = $prefix;
	}

	function getProcessRelations()
	{
		return $this->_processRelations;
	}

	function setProcessRelations($value)
	{
		$this->_processRelations = $value;
	}

	function getDefaultQuery()
	{
		return $this->_defaultQuery;
	}

	function setDefaultQuery( $value )
	{
		$this->_defaultQuery = $value;
	}

	function addField($field, $skipPrimaryCheck=false)
	{
		$field['type'] = strtolower( $field['type'] );

		if ( is_null( __Config::get( 'SITE_ID' ) ) && $field[ 'type' ] == AR_TYPE_SITEID )
		{
			return;
		}

		if ($skipPrimaryCheck===false)
		{
			if (array_key_exists('primaryKey', $field) && $field['primaryKey']===true)
			{
				if (is_null($this->_tablePrimaryKey))
				{
					$this->_tablePrimaryKey = $field['name'];
					$field['defaultValue'] = NULL;
					$field['defaultInsertValue'] = NULL;
				}
				else
				{
					// TODO:
					// visualizzare un errore perch� pu� esserci solo una chiave primaria
				}
			}
		}

		// inserire le chiavi mancanti dentro $field
		$fieldName = $field['name'];
		$field['canEncode'] = true;
		if ( !empty( $field['filter'] ) )
		{
			$field['filter'] = org_glizy_ObjectFactory::createObject( $field['filter'] );
		}

		if (!array_key_exists('type', $field)) $field['type'] = 'string';

		if ($field['type']== AR_TYPE_VERSIONDATE )
		{
			$field['type'] = AR_TYPE_DATE;
			$field['versionDate'] = true;
			$field['defaultInsertValue'] = 'NOW()';
			$field['defaultUpdateValue'] = 'NOW()';
		}
		else if ($field['type']== AR_TYPE_VERSIONSTATUS)
		{
			$field['type'] = AR_TYPE_ENUM;
			$field['values'] = array('PUBLISHED', 'DRAFT', 'OLD');
			$field['versionField'] = true;
			$field['defaultSelectValue'] = 'PUBLISHED';
			$field['defaultValue'] = NULL;
		}
		else if ($field['type']== AR_TYPE_LANGUAGE )
		{
			$application = &org_glizy_ObjectValues::get('org.glizy', 'application');
			$field['type'] = AR_TYPE_INTEGER;
			$field['languageField'] = true;
			$field['defaultSelectValue'] = $application->getLanguageId();
		}
		else if ($field['type']==AR_TYPE_USER)
		{
			$application = &org_glizy_ObjectValues::get('org.glizy', 'application');
			$user = &$application->getCurrentUser();
			$field['type'] = AR_TYPE_INTEGER;
			$field['userField'] = true;
			$field['defaultInsertValue'] = $user->id;
		}
		else if ($field['type'] == AR_TYPE_INT)
		{
			$field['type'] = AR_TYPE_INTEGER;
		}
		else if ($field['type'] == AR_TYPE_RICHTEXT || $field['type'] == AR_TYPE_TEXT )
		{
			$field['type'] = AR_TYPE_STRING;
			$field['canEncode'] = false;
		}
		else if ($field['type'] == AR_TYPE_SITEID)
		{
			$siteId = org_glizy_Config::get( 'SITE_ID' );
			if ( is_null( $siteId ) )
			{
				return;
			}
			$this->_siteIdField = $fieldName;
			$field['defaultSelectValue'] = $siteId ;
			$field['defaultValue'] = $siteId ;
		}
		else if ($field['type']== AR_TYPE_ACL)
		{
			$field['type'] = AR_TYPE_VIRTUAL;
			$this->_aclField = $fieldName;
			// definisce la relazione
			$this->addRelation(array('type' => 'joinTable', 'name' => 'rel_acl', 'className' => 'org.glizy.models.Join', 'field' => 'join_FK_source_id', 'destinationField' => 'join_FK_dest_id',  'bindTo' => $fieldName , 'objectName' => $this->getTableNameWithoutPrefix().'#acl'));
		}
		else if ($field['type']=='category')
		{
			if ( !__Config::get( 'CATEGORY_ENABLED' ) )
			{
				return;
			}
			$field['type'] = AR_TYPE_VIRTUAL;
			$this->_aclCategories = $fieldName;
			// definisce la relazione
			$this->addRelation(array('type' => 'joinTable', 'name' => 'rel_section', 'className' => 'org.glizy.models.Join', 'field' => 'join_FK_source_id', 'destinationField' => 'join_FK_dest_id',  'bindTo' => $fieldName , 'objectName' => $this->getTableNameWithoutPrefix().'#acl_category'));
		}

		$this->_fieldsList[$fieldName] = $field;
		$this->$fieldName = array_key_exists('defaultValue', $field) ? $field['defaultValue'] : NULL;

		if (array_key_exists('relation', $field))
		{
			$relation = $field['relation'];
			$relation['field'] = $fieldName;
			$this->addRelation($relation);
		}

	}

	function emptyRecord()
	{
		foreach ($this->_fieldsList as $k=>$v)
		{
			$this->$k = array_key_exists('defaultValue', $v) ? $v['defaultValue'] : NULL;;
		}
		foreach ($this->_relations as $k=>$v)
		{
			$this->$k = NULL;
		}
	}


	function modifyField($fieldName, $attribute, $value)
	{
		if ( array_key_exists($fieldName,$this->_fieldsList) )
		{
			if ($attribute=='relation')
			{
				// TODO
			}

			$this->_fieldsList[$fieldName][$attribute] = $value;
			$this->addField($this->_fieldsList[$fieldName]);
			$this->_makeSql('SELECT');
		}
	}

	function getFieldType($fieldName)
	{
		if (array_key_exists($fieldName,$this->_fieldsList))
		{
			return $this->_fieldsList[$fieldName]['type'];
		}
		else
		{
			return NULL;
		}
	}

	function removeField()
	{
		// TODO
	}

	function addRelation($options)
	{
		assert(isset($options['name']));

		if ( empty( $options['objectName'] ) )
		{
			$options['objectName'] = $this->getTableNameWithoutPrefix().'#'.$options['name'];
		}
		$this->_relations[$options['name']] = $options;
		$this->{$options['name']} = NULL;
	}

	function modifyRelation()
	{
		// TODO
	}

	function removeRelation()
	{
		// TODO
	}

	function invalidateRelations()
	{
		$this->_relationBuilded = false;
		foreach ($this->_relations as $k=>$v)
		{
			$this->$k = NULL;
		}
	}

	function buildAllRelations( $build = true )
	{
		if ($this->_processRelations && !$this->_relationBuilded)
		{
			$this->_relationBuilded = true;
			// risolve le relazioni
			foreach ($this->_relations as $k=>$v)
			{
				$relation = org_glizy_dataAccess_Relation::createObject($this, $v);
				if ( $build ) $relation->build();
				$this->$k = $relation;
			}

		}
	}

	function _saveAllRelations($preSave=true)
	{
		// TODO
		// quando si fa l'update anche delle relazioni
		// c'� da controllare che non si verifichino errori
		// in questo caso c'� da segnalarlo
		foreach ($this->_relations as $k=>$v)
		{
			if (is_object($this->$k))
			{
				if ($preSave==true)
				{
					$this->$k->preSave();
				}
				else
				{
					$this->$k->postSave();
				}
			}
		}
	}

	function _deleteAllRelations()
	{
		foreach ($this->_relations as $k=>$v)
		{
			if (is_object($this->$k))
			{
				$this->$k->delete();
			}
		}

		$this->_relationBuilded = false;
	}

	function buildRelation($name, $params=array())
	{
		//$relation = &new ActiveRecord_has_one($this, $this->_relations[$name]['key'], $this->_relations[$name]['className']);
		$relation = &org_glizy_dataAccess_relation::createObject($this, $this->_relations[$name]);
		$relation->build($params);
		$this->$name = &$relation;
	}

	function createRelation($name, $params=array())
	{
		// force to true the process relation flag
		$this->setProcessRelations(true);
		$relation = &org_glizy_dataAccess_relation::createObject($this, $this->_relations[$name]);
		$relation->create($params);
		$this->$name = &$relation;
	}

	function bindRelationObject($name, &$object)
	{
		// force to true the process relation flag
		$this->setProcessRelations(true);
		$relation = &org_glizy_dataAccess_relation::createObject($this, $this->_relations[$name]);
		$relation->bind($object);
		$this->$name = $relation;
	}

	function getId()
	{
		if (is_null($this->_tablePrimaryKey))
		{
			// TODO:
			// visualizzare un errore perch� pu� esserci solo una chiave primaria

			return NULL;
		}
		$primarykey = $this->_tablePrimaryKey;
		return $this->$primarykey;
	}

	function setId($value)
	{
		$primarykey = $this->_tablePrimaryKey;
		$this->$primarykey = $value;
	}


	function getPrimarykey()
	{
		return $this->_tablePrimaryKey;
	}

	function isNew()
	{
		return empty( $this->{$this->_tablePrimaryKey} );
	}

	function load( $id=NULL, $queryName=NULL, $params=array() )
	{
		org_glizy_dataAccess_DataAccess::selectDB( $this->_connNumber );

		$this->error = false;
		if (is_null($id))
		{
			$id = $this->getId();
		}

		if ( !is_null( $queryName ) )
		{
			$sql = $this->_getQuerySqlString( $queryName );
			//if ( stristr($sql, $this->getPrimarykey() ) === FALSE  && !is_null( $id ) )
			if ( stristr($sql, '?' ) === FALSE && !is_null( $id ) )
			{
				$sql .= stristr($sql, 'WHERE' ) === FALSE ? ' WHERE ' : ' AND ';
				$sql .= $this->getPrimarykey().' = ?';
			}

			if ( !is_null( $id ) )
			{
				$params = array_merge( $params , array( $id ) ) ;
			}

			$rs = &$this->_execute( $sql, $params );

		}
		else if ( $this->_defaultQuery == "SELECT" || $this->_defaultQuery == "All")
		{
			$rs = &$this->_execute($this->_getSqlString( "SELECT" ), array($id));
		}
		else
		{
			$sql = $this->_getQuerySqlString( $this->_defaultQuery );
			if ( stristr($sql, $this->getPrimarykey() ) === FALSE  && !is_null( $id ) )
			{
				$sql .= stristr($sql, 'WHERE' ) === FALSE ? ' WHERE ' : ' AND ';
				$sql .= $this->getPrimarykey().' = ?';
			}

			if ( !is_null( $id ) )
			{
				$params = array_merge( $params , array( $id ) ) ;
			}

			$rs = &$this->_execute( $sql, $params );
		}

		$rsFields = $rs->fields;
		if ($rsFields)
		{
			$this->loadFromArray($rsFields);
			$this->buildAllRelations();
			return true;
		}
		else
		{
			// TODO
			// visualizzare errore
			//			$e = ADODB_Pear_Error();
			//			echo '<p>',$e->message,'</p>';
			$this->error = true;
			return NULL;
		}
	}


	function loadFromArray($values)
	{
		$this->_relationBuilded = false;

		// mappa i risulatato all'oggetto
		if ( is_array( $values ) )
		{
			foreach ($values as $k=>$v)
			{
				if ( !empty( $k ) )
				{
					$this->$k = $v;
					if ( !isset( $this->_fieldsList[ $k ] ) )
					{
						$this->addField( array ( 'name' => $k, 'type' => AR_TYPE_VIRTUAL ) );
					}
				}
			}
		}

		foreach ($this->_fieldsList as $k=>$v)
		{
			if (is_array($values) && array_key_exists($k, $values))
			{

				$value = $values[$k];
				if (is_null($value) && isset($this->_fieldsList[$k]['notNull']) && $this->_fieldsList[$k]['notNull'])
				{
					$value = $this->_fieldsList[$k]['defaultValue'];
				}
				if ($this->_fieldsList[$k]['type']==AR_TYPE_DATE || $this->_fieldsList[$k]['type']==AR_TYPE_DATETIME)
				{
					$value = $this->_validateDate( $value, $this->_fieldsList[$k]['type'] );
				}
				else if ($this->_fieldsList[$k]['type']==AR_TYPE_INTEGER)
				{
					$value = intval($value);
                    if ( isset($this->_fieldsList[$k]['zeroIsNull']) && $this->_fieldsList[$k]['zeroIsNull'] === true && $value == 0 ) $value = "";
				}
				$this->$k = $value;
			}
			else
			{
				if ($v['type']=='virtual' && isset($v['bindTo']))
				{
					if (isset($values[$v['bindTo']]))
					{
						$this->$k = $values[$v['bindTo']];
					}
				}
			}
		}
		foreach ($this->_relations as $k=>$v)
		{
			if (array_key_exists($k, $values))
			{
				$this->$k = $values[$k];
			}
		}
	}

	function find($values=array())
	{
		org_glizy_dataAccess_DataAccess::selectDB( $this->_connNumber );
		$this->loadFromArray($values);
		$this->error = false;

		$tempFields		= array();
		$tempWhereValue = array();
		$tempWhereCond = array();

		foreach ($this->_fieldsList as $k=>$v)
		{
			if ($v['type']==AR_TYPE_VIRTUAL) continue;
			$tempFields[] = $k;
			if (!is_null($this->$k) && ($v['defaultValue']!=$this->$k || array_key_exists( $k, $values ) ) )
			{
				$tempWhereValue[] = $this->$k;
				$tempWhereCond[] = $k.'=?';
			}
			if ($v['type']==AR_TYPE_SITEID)
			{
				$tempWhereValue[] = $this->$k;
				$tempWhereCond[] = $k.'=?';
			}
		}

		$sql = '';
		$sql .= 'SELECT ';
		$sql .= implode(',', $tempFields);
		$sql .= ' FROM '.$this->getTableName();
		$sql .= ' WHERE '.implode(' AND ', $tempWhereCond).' LIMIT 1';

		$rs = &$this->_execute($sql, $tempWhereValue);
		$rsFields = $rs->fields;
		if ($rsFields)
		{
			$this->loadFromArray($rsFields);
			$this->buildAllRelations();
			return true;
		}
		else
		{
			$this->error = true;
			return false;
		}
	}

	function loadRecord($queryName, $params=array())
	{
		org_glizy_dataAccess_DataAccess::selectDB( $this->_connNumber );
		$sql = $this->_getQuerySqlString($queryName);
		$rs = &$this->_execute($sql, $params);
		$rsFields = $rs->fields;
		if ($rsFields)
		{
			$this->loadFromArray($rsFields);
			$this->buildAllRelations();
			return true;
		}
		else
		{
			// TODO
			// visualizzare errore
			//			$e = ADODB_Pear_Error();
			//			echo '<p>',$e->message,'</p>';
			$this->error = true;
			return NULL;
		}
	}


	function save($values=NULL, $forceNew=false)
	{
		org_glizy_dataAccess_DataAccess::selectDB( $this->_connNumber );
		if (!is_null($values)) $this->loadFromArray($values);

		if ($this->_processRelations)
		{
			$this->buildAllRelations();
			$this->_saveAllRelations(true);
		}

		$result = false;
		if ( $this->isNew() || $forceNew )
		{
			$evt = array('type' => GLZ_EVT_AR_INSERT_PRE.'@'.$this->getClassName(), 'data' => $this);
			$this->dispatchEvent($evt);
			$result = $this->_insert($values);
			$evt = array('type' => GLZ_EVT_AR_INSERT.'@'.$this->getClassName(), 'data' => $this);
			$this->dispatchEvent($evt);
		}
		else
		{
			$evt = array('type' => GLZ_EVT_AR_UPDATE_PRE.'@'.$this->getClassName(), 'data' => $this);
			$this->dispatchEvent($evt);
			$result = $this->_update($values);
			$evt = array('type' => GLZ_EVT_AR_UPDATE.'@'.$this->getClassName(), 'data' => $this);
			$this->dispatchEvent($evt);
		}
		if ($this->_processRelations) $this->_saveAllRelations(false);

		return $result;
	}

	function delete($id=NULL)
	{
		org_glizy_dataAccess_DataAccess::selectDB( $this->_connNumber );

		if (is_null($id))
		{
			$id = $this->getId();
		}

		if ( is_array( $id ) )
		{
			$tempWhereValue = array();
			$tempWhereCond = array();
			foreach ($this->_fieldsList as $k=>$v)
			{
				if ($v['type']==AR_TYPE_VIRTUAL) continue;
				if ( array_key_exists( $k, $id ) )
				{
					$tempWhereValue[] = $id[ $k ];
					$tempWhereCond[] = $k.'=?';
				}
			}

			$sql = 'DELETE FROM '.$this->getTableName();
			$sql .= ' WHERE '.implode(' AND ', $tempWhereCond);
			$rs = $this->_execute( $sql, $tempWhereValue );
		}
		else
		{
			$this->setId( $id );
			$rs = $this->_execute($this->_getSqlString('DELETE'), array($id));
		}

		$evt = array('type' => GLZ_EVT_AR_DELETE.'@'.$this->getClassName(), 'data' => $this);
		$this->dispatchEvent($evt);
		if ($this->_processRelations)
		{
			$this->buildAllRelations( false );
			$this->_deleteAllRelations();
		}
		$this->emptyRecord();

		if ($rs) return true;
		else {
			// TODO
			// visualizzare errore
			//			$e = ADODB_Pear_Error();
			//			echo '<p>',$e->message,'</p>';
			return false;
		}

	}

	function getValuesAsArray($getRelationValues=false, $getVirtualField=true, $encode=false)
	{
		$result = $this->_collectFieldsValues($getRelationValues, $getVirtualField, $encode);
		return $result;
	}


	function _getSqlString($type='SELECT')
	{
		if (!array_key_exists($type, $this->_sql))
		{
			$this->_makeSql($type);
		}
		return $this->_sql[$type];
	}


	function _makeSql($type)
	{
/*		if (is_null($this->_tablePrimaryKey))
		{
			// TODO:
			// visualizzare un errore
			return NULL;
		}
*/
		$tempFields 		= array();
		$tempInsertField 	= array();
		foreach ($this->_fieldsList as $k=>$v)
		{
			if ($v['type']==AR_TYPE_VIRTUAL) continue;
			$tempFields[] = $k;
			$tempInsertField[] = '?';
		}

		$sql = '';
		switch ($type)
		{
			case 'SELECT':
				$sql .= 'SELECT ';
				$sql .= implode(',', $tempFields);
				$sql .= ' FROM '.$this->getTableName();
				$sql .= ' WHERE '.$this->_tablePrimaryKey.' = ?';
				$sql .= $this->_addAclClause();
				break;
			case 'INSERT':
				$sql .= 'INSERT INTO '.$this->getTableName().' ';
				$sql .= '('.implode(',', $tempFields).')';
				$sql .= ' VALUES ( '.implode(',', $tempInsertField).');';
				break;
			case 'INSERT_QUEUE':
				$sql .= 'INSERT INTO '.$this->getTableName().' ';
				$sql .= '('.implode(',', $tempFields).')';
				$sql .= ' VALUES ';
				break;
			case 'UPDATE':
				$sql .= 'UPDATE '.$this->getTableName().' SET ';
				$sql .= implode('= ?,', $tempFields).'= ? ';
				$sql .= ' WHERE '.$this->_tablePrimaryKey.' = ?';
				break;
			case 'DELETE':
				$sql .= 'DELETE ';
				$sql .= ' FROM '.$this->getTableName();
				$sql .= ' WHERE '.$this->_tablePrimaryKey.' = ?';
				break;
			default:
				// TODO
				// visualizzare errore
				break;
		}
		$this->_sql[$type] = $sql;
	}


	function _insert($values=NULL)
	{
		$insertSql = $this->_getSqlString('INSERT');
		$values = $this->_collectFieldsValues(false, false);
		$queryParams 		= array();
		foreach ($this->_fieldsList as $k=>$v)
		{
			if ($v['type']==AR_TYPE_VIRTUAL) continue;
			if (array_key_exists('defaultInsertValue', $v) )
			{
				$values[$k] = is_null( $values[$k] ) ? $v['defaultInsertValue'] : $values[$k];
				if ( ( $v['type']==AR_TYPE_DATE || $v['type']==AR_TYPE_DATETIME ) && ( empty ( $values[$k] ) || $values[$k] === "0000-00-00 00:00:00" || $values[$k] === "0000-00-00" ) ) $values[$k] = $v['defaultInsertValue'];
				if ($values[$k]=='NOW()' && ( $v['type']==AR_TYPE_DATE || $v['type']==AR_TYPE_DATETIME ) )
				{
					$explodedSql = explode('?', $insertSql);
					$numParams = count($queryParams);
					$numParts = count($explodedSql);
					$explodedSql[$numParams] .= $this->_createInsertDateNative( $v['type'] ).($numParams==$numParts ? '' : ',');
					array_splice($explodedSql, $numParams+1, 1);
					if ($explodedSql[$numParts-2]!=');') {
						$explodedSql[$numParts-2] = rtrim($explodedSql[$numParts-2], ',').');';
					}
					$insertSql = implode('?', $explodedSql);
					continue;
				}
			}
			if ( $v['type']==AR_TYPE_DATE || $v['type']==AR_TYPE_DATETIME )
			{
				if (  empty($values[$k]) )
				{
					$values[$k] =  $this->_createEmptyDate( $v['type'] );
				}
				else
				{
					$values[$k] = glz_localeDate2default($values[$k] );
				}
			}
			if (is_null($values[$k]) && array_key_exists('null', $v) && $v['null']==false)
			{
				$values[$k] = '';
			}

			if ( $v['type'] == AR_TYPE_INTEGER ) {
				if (!is_null($values[$k] ) && $values[$k] == '' ) $values[$k] = 0;
				else if ($values[$k]===true) $values[$k] = 1;
				else if ($values[$k]===false) $values[$k] = 0;
			}

			$queryParams[] = $values[$k];
		}
		if ( is_null( $this->queue ) )
		{
			$this->_execute($insertSql, $queryParams);
			$this->setId($this->_conn->Insert_ID());
			return $this->getId();
		}
		else
		{
			$sql = array();
			foreach( $queryParams as $v )
			{
				$sql[] = org_glizy_dataAccess_DataAccess::qstr( $v );
			}
			$this->queue->push( '('.implode( ', ', $sql ).')' );
			return true;
		}
	}

	function _update($values=NULL)
	{
		$values = $this->_collectFieldsValues(false, false);
		$queryParams 		= array();
		foreach ($this->_fieldsList as $k=>$v)
		{
			if ($v['type']==AR_TYPE_VIRTUAL) continue;
			if (array_key_exists('defaultUpdateValue', $v))
			{
				$values[$k] = $v['defaultUpdateValue'];

				if ($values[$k]=='NOW()' && ( $v['type']==AR_TYPE_DATE || $v['type']==AR_TYPE_DATETIME ) )
				{
					$values[$k] = $this->_createInsertDate( $v['type'] );
				}
			}
			if ( $v['type']==AR_TYPE_DATE || $v['type']==AR_TYPE_DATETIME )
			{
				if (  empty($values[$k]) )
				{
					$values[$k] =  $this->_createEmptyDate( $v['type'] );
				}
				else
				{
					$values[$k] = glz_localeDate2default($values[$k] );
				}
			}
			if ( $v['type'] == AR_TYPE_INTEGER ) {
				if ($values[$k]===true) $values[$k] = 1;
				else if ($values[$k]===false) $values[$k] = 0;
			}

			if (is_null($values[$k]) && array_key_exists('null', $v) && $v['null']==false)
			{
				$values[$k] = '';
			}

			$queryParams[] = $values[$k];
		}
		$queryParams[] = $this->getId();
		// TODO
		// controllare che l'ID sia valido
		$rs = &$this->_execute($this->_getSqlString('UPDATE'), $queryParams);

		if ($rs)
		{
			return true;
		}
		else
		{
			// TODO
			// visualizzare errore
			//			$e = ADODB_Pear_Error();
			//			echo '<p>',$e->message,'</p>';
			return false;
		}
	}


	function _collectFieldsValues($getRelationValues=false, $getVirtualField=true, $encode=false)
	{
		$values = array();
		$relationsKeys = array_keys($this->_relations);

		foreach ($this->_fieldsList as $k=>$v)
		{
			if ($v['type']==AR_TYPE_VIRTUAL && ((in_array($k, $relationsKeys) && !$getRelationValues) || (!$getVirtualField)))
			{
				continue;
			}
			if ($encode && $v['canEncode'] && $v['type']!= AR_TYPE_VIRTUAL )
			{
				$values[$k] = glz_encodeOutput($this->$k);
			}
			else
			{
				if ( !empty( $v['filter'] ) )
				{
					$v['filter']->apply( $this->$k, $this );
				}
				$values[$k] = $this->$k;
			}
		}

		if ($getRelationValues)
		{
			$this->buildAllRelations();
			foreach ($this->_relations as $k=>$v)
			{
				if (!is_object($this->$k)) $values[$k] = $this->$k;
				else
				{
					if ( method_exists( $this->$k, 'collectFieldsValues' ) )
					{
						$values[$k] = $this->$k->collectFieldsValues( $getVirtualField, $encode );
					}
					else
					{
						$values[$k] = NULL;
					}
				}
			}
		}
		return $values;
	}

	function getFieldValue($name)
	{
		$this->buildAllRelations();
		return isset($this->$name) ? $this->$name : '';
	}

	function getFieldValueByRegexp($name)
	{
		$this->buildAllRelations();

		foreach ($this->_fieldsList as $k=>$v)
		{
			if ($v['type']==AR_TYPE_VIRTUAL )
			{
				continue;
			}
			if ( strpos( $k, $name ) !== false )
			{
				return $this->$k;
			}
		}
		return '';
	}



	function getFieldDefaultValue($name)
	{
		return $this->_fieldsList[$name]['defaultValue'];
	}


	function setFieldValue($name, $value)
	{
		//if (isset($this->$name))
		//{
			$this->$name = $value;
		//}
	}

	// TODO
	// rimuovere la funzione perch� � un duplicato di getPrimaryKey
	function getPrimaryKeyName()
	{
		return $this->_tablePrimaryKey;
	}

	/*****/


	function _replaceSqlTag($sql)
	{
		$application = &org_glizy_ObjectValues::get('org.glizy', 'application');
		if (strpos($sql, '##TABLE_NAME##')!==false) $sql = str_replace('##TABLE_NAME##', $this->getTableName(), $sql);
		$sql = str_replace('##SELECT_ALL##', 		$this->_getQuerySqlString('all', false), $sql);
		$sql = str_replace('##TABLE_PREFIX##', 		$this->_tablePrefix, $sql);
		$sql = str_replace('##SITE_ID##', 			org_glizy_Config::get('SITE_ID'), $sql);
		if (!is_null($application))
		{
			$user = &$application->getCurrentUser();
			$sql = str_replace('##USER_ID##', 		$user->id, $sql);
			$sql = str_replace('##USER_GROUP_ID##', $user->groupId, $sql);
			$sql = str_replace('##LANGUAGE_ID##', 		$application->getLanguageId(), $sql);
			if (method_exists($application, 'getEditingLanguageId')) $sql = str_replace('##EDITING_LANGUAGE_ID##', 	$application->getEditingLanguageId(), $sql);
		}
		if( strpos($sql, "??") !== false )
        {
      		preg_match_all( "/\?\?([^\?]*)\?\?/U", $sql, $resmatch );
            foreach( $resmatch[1] as $varname)
            {
            	if ( strpos( $varname, '.' ) !== false )
            	{
	            	list( $filterName, $name ) = explode( '.', $varname );
	            	$value = call_user_func( $filterName, __Request::get($name) );
            	}
            	else
            	{
            		$value = __Request::get($varname);
            	}
            	$sql = str_replace("??$varname??",  org_glizy_dataAccess_DataAccess::qstr( $value ), $sql);
            }
        }
		return $sql;
	}

	function &loadQuery($queryName, $options=array())
	{
		org_glizy_dataAccess_DataAccess::selectDB( $this->_connNumber );
		if ($this->_conn->debug) {
			echo '<b>'.$queryName.'</b><br>';
		}

		if ( $this->enableLog )
        {
            $eventInfo = array('type' => GLZ_LOG_EVENT, 'data' => array(
                    'level' => GLZ_LOG_DEBUG,
                    'group' => '',
                    'message' => 'loadQuery: '.$queryName
            ));
            $evt = org_glizy_ObjectFactory::createObject( 'org.glizy.events.Event', $this, $eventInfo );
            org_glizy_events_EventDispatcher::dispatchEvent( $evt );
        }
		if ( !isset( $options[ 'sql' ] ) )
		{
			$sql = $this->_getQuerySqlString($queryName);
			$sql = rtrim($sql, ';');
			$params = isset($options['params']) ? $options['params'] : array();
			$checkIntegrity = isset($options['checkIntegrity']) ? $options['checkIntegrity'] : true;

			if (!isset($options['filters'])) $options['filters'] = array();
			if (!is_array($options['filters'])) $options['filters'] = array($options['filters']);

			if (strpos($sql, 'WHERE')===false)
			{
				foreach ($this->_fieldsList as $k=>$v)
				{
					if (isset($v['defaultSelectValue']) && !isset($options['filters'][$k]) )
					{
						if ( $v['languageField'] === true || $v['versionField']== true || $options['default'] !== false)
						{
							$options['filters'][$k] = $v['defaultSelectValue'];
						}
					}
				}
			}
			// se � definito un sito forza la condizione
			if ( !is_null( __Config::get( 'SITE_ID' ) ) && !is_null( $this->_siteIdField ) && !isset( $options['filters'][ $this->_siteIdField ] ) )
			{
				$options['filters'][ $this->_siteIdField ] = __Config::get( 'SITE_ID' );
			}
			$sqlWhere = '(';
			foreach ($options['filters'] as $filterName=>$filterValue)
			{
				if (!is_string($filterName))
				{
					$sqlWhere .= $filterValue.' AND ';
					continue;
				}
				else if (!isset($this->_fieldsList[$filterName]) && $checkIntegrity ) continue;

				if ( $this->_fieldsList[$filterName]['type'] == AR_TYPE_DATE || $this->_fieldsList[$filterName]['type'] == AR_TYPE_DATETIME )
				{
					if ( is_array($filterValue) && $filterValue[0] == "=" )
					{
						$filterValue = $filterValue[1];
					}
				}

				if (!is_array($filterValue))
				{
					switch ($this->_fieldsList[$filterName]['type'])
					{
						case AR_TYPE_DATE:
						case AR_TYPE_DATETIME:
							$sqlWhere .= $filterName.' LIKE '.org_glizy_dataAccess_DataAccess::qstr('%'.$filterValue.'%').' AND ';
							break;
						case AR_TYPE_INTEGER:
						case AR_TYPE_SITEID:
						case AR_TYPE_ENUM:
							if ($filterValue!='') $sqlWhere .= $filterName.'= '.org_glizy_dataAccess_DataAccess::qstr($filterValue).' AND ';
							break;
						default:
							if ($this->_fieldsList[$filterName]['canEncode'] === false)
							{
								$filterValue = glz_encodeOutput($filterValue);
							}
							if ( !empty( $filterValue ) )
							{
								$sqlWhere .= $filterName.' LIKE '.org_glizy_dataAccess_DataAccess::qstr('%'.$filterValue.'%').' AND ';
							}
							break;
					}
				}
				else
				{
					$sqlWhere .= $filterName.' '.$filterValue[0].(isset($filterValue[1]) ? ' '.org_glizy_dataAccess_DataAccess::qstr($filterValue[1]) : '').' AND ';
				}
			}
			$sqlWhere .= '1=1) ';

			// se � definita l'acl
			$sqlWhere .= $this->_addAclClause();
			if (isset($options['categories'])) $sqlWhere .= $this->_addAclCategoriesClause( $options['categories'] );

			if (strripos ( $sql, 'WHERE' ) === false)
			{
				if (strripos ( $sql, 'ORDER' )===false && strripos ( $sql, 'GROUP BY' )===false )
			{
				$sql .= ' WHERE '.$sqlWhere;
			}
				else if (strripos ( $sql, 'GROUP BY' )===false )
				{
					$sql = str_replace('ORDER', ' WHERE '.$sqlWhere.' ORDER ', $sql);
				}
				else
				{
					$sql = str_replace('GROUP BY', ' WHERE '.$sqlWhere.' GROUP BY', $sql);
				}
			}
			else
			{
				$sql = str_replace('WHERE', 'WHERE '.$sqlWhere.' AND ', $sql);
			}
			$sql = str_replace('##W##', 'WHERE', $sql);

			if (isset($options['group']))
			{
				if ( strripos( $sql, 'GROUP BY') !== false )
				{
					$sql .= ' GROUP BY '.$options['group'];
				}
			}

			if (isset($options['order']))
			{
				// TODO
				// controllare che i valori passati in orderBy siano delle colonne valide
				$order = is_array($options['order']) ? implode(',', $options['order']) : $options['order'];

				if ( strripos( $sql, 'ORDER') === false )
				{
					$order = ' ORDER BY '.$order;
				}
				else
				{
					$order = ', '.$order;
				}
				// if ( strripos( $sql, 'GROUP BY') !== false )
				// {
				// 				list( $p1, $p2 ) = explode( 'GROUP BY', $sql );
				// 				$sql  = $p1.$order.' GROUP BY '.$p2;
				// }
				// else
				// {
					$sql  .= $order;
				// }
			}
		}
		else
		{
			$sql = $options[ 'sql' ];
		}

		if ( isset( $options[ 'numRows' ] ) && $options[ 'numRows' ] == true )
		{
			if ( stripos( $sql, 'SELECT SQL_CALC_FOUND_ROWS' ) === false )
			{
				$pos = stripos( $sql, 'SELECT' );
				$sql = 'SELECT SQL_CALC_FOUND_ROWS '.substr( $sql, $pos + 7 );
			}
		}

		// esegue la paginazione
		if (isset($options['limit']) && !empty($options['limit']))
		{
			if (is_string($options['limit']))
			{
				list($limitStart, $limitLength) = explode(',', $options['limit']);
			}
			else
			{
				if (!isset($options['limit']['start']))
				{
					$limitStart = $options['limit'][0];
					$limitLength = $options['limit'][1];
				}
				else
				{
					$limitStart = $options['limit']['start'];
					$limitLength = $options['limit']['pageLength'];
				}
			}
			if ( stripos( $sql, 'limit' ) )
			{
				$sql = preg_replace( '/limit\s*\d*,\s*?\d$/i', '', $sql );
			}
			$rs = &$this->_execute($sql, $params, $limitLength, $limitStart );
		}
		else
		{
			$rs = &$this->_execute($sql, $params);
		}

		if ($this->_conn->ErrorNo())
		{
			$this->triggerError($this->getClassName().': '.$this->_conn->ErrorMsg()." ".$sql);
		}


		$recordIterator = new org_glizy_dataAccess_RecordIterator($rs, get_class($this));

		if ( stripos( $sql, 'SELECT SQL_CALC_FOUND_ROWS' ) !== false )
		{
			$rs2 = $this->execSql( 'SELECT FOUND_ROWS() as tot;' );
			$recordIterator->setCount( $rs2->fields[ 'tot' ] );
		}

		return $recordIterator;
	}

	function &loadOneRecord($queryName, $params=array())
	{
		$recordIterator = &$this->loadQuery($queryName, $params);
		return $recordIterator->current();
	}

	function &findBySql($sql, $params=array())
	{
		org_glizy_dataAccess_DataAccess::selectDB( $this->_connNumber );
		$sql = $this->_replaceSqlTag($sql);
		$rs = &$this->_execute($sql, $params);
		$recordIterator = new org_glizy_dataAccess_RecordIterator($rs, get_class($this));

		if ( stripos( $sql, 'SELECT SQL_CALC_FOUND_ROWS' ) !== false )
		{
			$rs2 = $this->execSql( 'SELECT FOUND_ROWS() as tot;' );
			$a = $rs2->GetArray();
			$recordIterator->setCount( $a[ 0 ][ 'tot' ] );
		}

		return $recordIterator;
	}

	function execSql($sql, $params=array())
	{
		org_glizy_dataAccess_DataAccess::selectDB( $this->_connNumber );
		$sql = $this->_replaceSqlTag($sql);
		$result = $this->_execute($sql, $params);
		return $result;
	}

	function execSqlByName($queryName, $params=array())
	{
		org_glizy_dataAccess_DataAccess::selectDB( $this->_connNumber );
		$sql = $this->_getQuerySqlString($queryName);
		$result = $this->_execute($sql, $params);
		return $result;
	}


	function addQuery($name, $sql)
	{
		$this->_querySql[strtolower($name)] = $sql;
	}

	function getQuery($name)
	{
		return $this->_querySql[strtolower($name)];
	}

	function _getQuerySqlString($queryName, $replaceTags=true)
	{
		$queryName = strtolower($queryName);

		if (!array_key_exists('all', $this->_querySql))
		{
			$sql  = 'SELECT *';
			$sql .= ' FROM '.$this->_tableName;
			$this->addQuery('all', $sql);
		}

        $sql = '';
		if (!array_key_exists($queryName, $this->_querySql))
		{
            if ( method_exists( $this, $queryName ) )
    	    {
                $sql = call_user_func_array( array( $this, $queryName ), array() );
  		    }
		}
        else
        {
			// sostituisce le keywords riconosciute
  		    $sql = $this->_querySql[$queryName];
	    }

		if ($replaceTags)
		{
			$sql = $this->_replaceSqlTag( $sql );
		}

		return $sql;
	}

	function loadDictionary($field, $queryName=NULL, $skipEmpty=false, $delimiter='', $version='PUBLISHED')
	{
		$application = &org_glizy_ObjectValues::get('org.glizy', 'application');
		if (is_null($queryName))
		{
			$field = explode(',', $field);
			$filter = $this->_fieldsList[ count($field)>1 ? $field[1] : $field[0] ][ 'filter' ];
			$sql  = 'SELECT DISTINCT UPPER('.$field[0].') as k, '.(count($field)>1 ? $field[1] : $field[0]).' as v' ;
			$sql .= ' FROM '.$this->getTableName();
			$sql .= ' WHERE 1=1';
			foreach ($this->_fieldsList as $k=>$v)
			{
				if (isset($v['languageField']) && $v['languageField']===true)
				{
					$sql .= ' AND '.$k.'='.$application->getLanguageId();
				}
				if (isset($v['versionField']) && $v['versionField']===true)
				{
					$sql .= ' AND '.$k.'=\''.$version.'\'';
				}
				if (isset($v['defaultSelectValue']))
				{
					$sql .= ' AND '.$k.'=\''.$v['defaultSelectValue'].'\'';
				}
			}
			/*
			codice da verificare
			if ( !is_null( __Config::get( 'SITE_ID' ) ) && !is_null( $this->_siteIdField )  )
			{
				$sql .= ' AND '.$this->_siteIdField.'=\''.__Config::get( 'SITE_ID' ).'\'';
			}
			*/

			$sql .= ' ORDER BY v';
		}
		else
		{
			$filter = null;
			$sql = $this->_getQuerySqlString($queryName);
		}
		$result = array();
		$usedKeys = array();
		$rs = &$this->_execute($sql);
		while (!$rs->EOF)
		{
			if (!($skipEmpty && empty($rs->fields['v'])))
			{
				$value = $rs->fields['v'];
				if ( !empty( $filter ) )
				{
					$filter->apply( $value, $this );
				}
				if ( !$delimiter )
				{
					$result[] = array('key' => $rs->fields['k'], 'value' => $value );
				}
				else
				{
					$kk = explode( $delimiter, $rs->fields['k'] );
					$vv = explode( $delimiter, $value );
					$l = count( $kk );
					for( $i = 0; $i < $l; $i++ )
					{
						if ( !in_array( $kk[ $i ], $usedKeys ) )
						{
						 	$usedKeys[] = $kk[ $i ];
						 	$result[] = array('key' => $kk[ $i ], 'value' => $vv[ $i ] );
						}
					}
				}
			}
			$rs->MoveNext();
		}
		if ( $delimiter )
		{
			org_glizy_helpers_Array::arrayMultisortByLabel( $result, 'value' );
		}
		return $result;
	}


	function getVersionFieldName()
	{
		$fieldName = NULL;
		foreach ($this->_fieldsList as $k=>$v)
		{
			if (isset($v['versionField']) && $v['versionField']===true)
			{
				$fieldName = $k;
				break;
			}
		}
		return $fieldName;
	}

	function getLanguageFieldName()
	{
		$fieldName = NULL;
		foreach ($this->_fieldsList as $k=>$v)
		{
			if (isset($v['languageField']) && $v['languageField']===true)
			{
				$fieldName = $k;
				break;
			}
		}
		return $fieldName;
	}

	function _createInsertDate( $type )
	{
		if ( $type == AR_TYPE_DATE )
		{
			return date('Y-m-d');
		}
		else if ( $type == AR_TYPE_DATETIME )
		{
			return date('Y-m-d H:i:s');
		}
	}

	function _createInsertDateNative( $type )
	{
		if ( $type == AR_TYPE_DATE )
		{
			return 'CURDATE()';
		}
		else if ( $type == AR_TYPE_DATETIME )
		{
			return 'NOW()';
		}
	}

	function _createEmptyDate( $type )
	{
		if ( $type == AR_TYPE_DATE )
		{
			return date('0000-00-00');
		}
		else if ( $type == AR_TYPE_DATETIME )
		{
			return date('0000-00-00 00:00:00');
		}
	}

	function _validateDate( $value, $type )
	{
		if ($value!='0000-00-00' && $value!='0000-00-00 00:00:00' && !is_null($value) && !empty($value) )
		{
			if (strlen($value)>=10)
			{
				$reg = __T( $type == 'date' ? 'GLZ_TIME_TO_DATE_REGEXP' : 'GLZ_TIME_TO_DATETIME_REGEXP' );

				if ( is_array( $reg ) && preg_match( $reg[0], $value ) )
				{
                    $value = glz_defaultDate2locale( __T( $type == AR_TYPE_DATE ? 'GLZ_DATE_FORMAT' : 'GLZ_DATETIME_FORMAT' ), $value );
				}
			}
		}
		else
		{
			$value = '';
		}
		return $value;
	}

	function _addAclClause()
	{
		$application = &org_glizy_ObjectValues::get('org.glizy', 'application');
		$sql = '';
		if ( !is_null( $this->_aclField ) && !$application->isAdmin() )
		{
			$user = &$application->getCurrentUser();
			$sql = ' AND ( ( SELECT count(*) FROM '.$this->_tablePrefix.'joins_tbl WHERE join_objectName = \''.$this->getTableNameWithoutPrefix().'#acl\' AND join_FK_source_id = '.$this->getPrimaryKey().' ) = 0 ';
			$sql .= ' OR ';
			$sql .= ' ( SELECT join_id FROM '.$this->_tablePrefix.'joins_tbl WHERE join_objectName = \''.$this->getTableNameWithoutPrefix().'#acl\' AND join_FK_source_id = '.$this->getPrimaryKey().' AND join_FK_dest_id = \''.$user->groupId.'\' LIMIT 0,1) IS NOT NULL )';
		}
		return $sql;
	}

	function _addAclCategoriesClause( $categories )
	{
		$application = &org_glizy_ObjectValues::get('org.glizy', 'application');
		$sql = '';
		if ( !is_null( $this->_aclCategories ) && !empty( $categories ) && !$application->isAdmin() )
		{
			$user = &$application->getCurrentUser();
			$sql = ' AND ( ( SELECT count(*) FROM '.$this->_tablePrefix.'joins_tbl WHERE join_objectName = \''.$this->getTableNameWithoutPrefix().'#acl_category\' AND join_FK_source_id = '.$this->getPrimaryKey().' ) = 0 ';
			$sql .= ' OR ';
			$sql .= ' ( SELECT join_id FROM '.$this->_tablePrefix.'joins_tbl WHERE join_objectName = \''.$this->getTableNameWithoutPrefix().'#acl_category\' AND join_FK_source_id = '.$this->getPrimaryKey().' AND join_FK_dest_id IN('.$categories.') LIMIT 0,1) IS NOT NULL )';
		}
		return $sql;
	}

    /**
     * @param $sql
     * @param array $params
     * @param null $limitLength
     * @param null $limitStart
     * @return mixed
     */
	function _execute( $sql, $params = array(), $limitLength = null, $limitStart = null )
	{
		if ( $sql != 'SELECT FOUND_ROWS() as tot;' )
		{
			$this->lastSql = $sql;
		}

		$debugState = $this->_conn->debug;
		if ( $this->enableLog )
		{
			$this->_conn->debug = true;
		}

		$startTime = microtime();
		$sqlForLog = $sql;
		ob_start();
		if ( is_null( $limitLength ) )
		{
			$r = $this->_conn->execute( $sql, $params );
		}
		else
		{
			$r = $this->_conn->selectLimit( $sql, $limitLength, $limitStart, $params );
		}

		$sql = ob_get_clean();
		$this->_conn->debug = $debugState;

		$eventInfo = array(	'type' => GLZ_EVT_AR_EXEC_SQL,
							'data' => array(
							'level' => GLZ_LOG_DEBUG,
							'group' => '',
							'message' => array(	'sql' => $sqlForLog,
												'params' => $params,
												'startTime' => $startTime,
												'resultSet' => $r)
		));
		$evt = org_glizy_ObjectFactory::createObject( 'org.glizy.events.Event', $this, $eventInfo );
		org_glizy_events_EventDispatcher::dispatchEvent( $evt );

		if ( $this->_conn->debug || $this->enableLog )
		{
			if ( $this->enableLog )
			{
				$eventInfo = array('type' => GLZ_LOG_EVENT, 'data' => array(
						'level' => GLZ_LOG_DEBUG,
						'group' => '',
						'message' => strip_tags( $sql )
				));
				$evt = org_glizy_ObjectFactory::createObject( 'org.glizy.events.Event', $this, $eventInfo );
				org_glizy_events_EventDispatcher::dispatchEvent( $evt );
			}

			if ( $this->_conn->debug )
			{
				echo $sql."<br />\n\r";
			}
		}

		return $r;
	}

	function enableQueue()
	{
		$this->queue = $this->_conn->getQueueExecute();
		$this->queue->init( $this->_getSqlString( 'INSERT_QUEUE' ) );
        return true;
	}

	function executeQueue()
	{
        $result = $this->queue->execute();
		$this->queue = NULL;
        if (!$result) {
            return false;
        } else {
            return true;
        }
	}


}