<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_dataAccess_ActiveRecord2tables extends org_glizy_dataAccess_ActiveRecord
{
	var $_tablesName 		= array();
	var $_joinFields		= array();
	var $_tableDetailPrimaryKey 	= NULL;

	function __construct()
	{
		parent::__construct();
	}

	function getTableName()
	{
		return $this->_tablesName['mainTable'];
	}

	function setTableName($theName1, $theName2)
	{
		$this->_tablesName['mainTable'] 	= $this->_tablePrefix.$theName1;
		$this->_tablesName['detailTable'] 	= $this->_tablePrefix.$theName2;
	}

	function getJoinFields()
	{
		return $this->_joinFields;
	}

	function setJoinFields($theJoin1, $theJoin2)
	{
		$this->_joinFields['mainTable'] 	= $theJoin1;
		$this->_joinFields['detailTable'] = $theJoin2;
	}

	function addField($field)
	{
		if (isset($field['primaryKey']) && $field['primaryKey']===true)
		{

			if (isset($field['detailTable']) && $field['detailTable']===true)
			{
				if (is_null($this->_tableDetailPrimaryKey))
				{
					$this->_tableDetailPrimaryKey = $field['name'];
				}
				else
				{
					// TODO:
					// visualizzare un errore perché puà esserci solo una chiave primaria
				}
			}
			else
			{
				if (is_null($this->_tablePrimaryKey))
				{
					$this->_tablePrimaryKey = $field['name'];
				}
				else
				{
					// TODO:
					// visualizzare un errore perché puà esserci solo una chiave primaria
				}
			}
		}

		parent::addField($field, true);
	}

	function getDetailId()
	{
		if (is_null($this->_tablePrimaryKey))
		{
			// TODO:
			// visualizzare un errore perché puà esserci solo una chiave primaria

			return NULL;
		}
		$primarykey = $this->_tableDetailPrimaryKey;
		return $this->$primarykey;
	}

	function setDetailId($value)
	{
		$primarykey = $this->_tableDetailPrimaryKey;
		$this->$primarykey = $value;
	}

	function getDetailPrimarykey()
	{
		return $this->_tableDetailPrimaryKey;
	}

	function load($id=NULL, $idDetail=NULL, $queryName=NULL )
	{
		$this->error = false;

		$queryParams = array();
		if (is_null($id))
		{
			$id = $this->getId();
		}

		if (is_null($idDetail))
		{
			$idDetail = $this->getDetailId();
		}

		if ( is_null( $queryName ) )
		{
			$tempFields 		= array();
			$tempInsertField 	= array();
			foreach ($this->_fieldsList as $k=>$v)
			{
				if ($v['type']==AR_TYPE_VIRTUAL) continue;
				$tempFields[] = $k;
				$tempInsertField[] = '?';
			}

			$sql = '';
			$sql .= 'SELECT ';
			$sql .= implode(',', $tempFields);
			$sql .= ' FROM '.$this->_tablesName['mainTable'];
			$sql .= ' INNER JOIN '.$this->_tablesName['detailTable'].' ON ('.implode('=', $this->_joinFields).')';
			$sql .= ' WHERE 1=1';
			if (!is_null($id))
			{
				$sql .= ' AND '.$this->_tablePrimaryKey.' = ?';
				$queryParams[] = $id;
			}
			if (!is_null($idDetail))
			{
				$sql .= ' AND '.$this->_tableDetailPrimaryKey.' = ?';
				$queryParams[] = $idDetail;
			}
		}
		else
		{
			$sql = $this->_getQuerySqlString( $queryName );
		}
		$sql .= $this->_addAclClause();

		// TODO
		// se la query genera più record
		// bisogna generare un errore

		$rs = &$this->_execute($sql, $queryParams);
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
			if (!is_null($this->$k) && ($v['defaultValue']!=$this->$k))
			{
				$tempWhereValue[] = $this->$k;
				$tempWhereCond[] = $k.'=?';
			}
		}

		$sql = '';
		$sql .= 'SELECT ';
		$sql .= implode(',', $tempFields);
		$sql .= ' FROM '.$this->_tablesName['mainTable'];
		$sql .= ' INNER JOIN '.$this->_tablesName['detailTable'].' ON ('.implode('=', $this->_joinFields).')';
		$sql .= ' WHERE '.implode(' AND ', $tempWhereCond);
		$sql .= $this->_addAclClause();
		$sql .= ' LIMIT 1';

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
			return NULL;
		}
	}

	function save($values=NULL)
	{
		if ($this->_processRelations) $this->buildAllRelations();
		if (!is_null($values)) $this->loadFromArray($values);
		if ($this->_processRelations) $this->_saveAllRelations(true);

		// TODO
		// controllo della validità dei campi e se i campi "required" sono riempiti
		$primaryId = $this->getId();
		if (empty($primaryId))
		{
			$result1 = $this->_insertMain($values);
		}
		else
		{
			$result1 = $this->_updateMain($values);
		}

		$detailId = $this->getDetailId();
		if (empty($detailId))
		{
			$result2 = $this->_insertDetail($values);
			$evt = array('type' => GLZ_EVT_AR_INSERT.'@'.$this->getClassName(), 'data' => $this);
			$this->dispatchEvent($evt);
		}
		else
		{
			$result2 = $this->_updateDetail($values);
			$evt = array('type' => GLZ_EVT_AR_UPDATE.'@'.$this->getClassName(), 'data' => $this);
			$this->dispatchEvent($evt);
		}

		if ($this->_processRelations) $this->_saveAllRelations(false);

		return array($result1, $result2);
	}

	function delete($id=NULL)
	{
		org_glizy_dataAccess_DataAccess::selectDB( $this->_connNumber );
		if (is_null($id))
		{
			$id = $this->getId();
		}

		if (is_null($id))
		{
			// visualizzare errore
		}

		$this->setId( $id );
		$sql  = 'DELETE ';
		$sql .= ' FROM '.$this->_tablesName['mainTable'];
		$sql .= ' WHERE '.$this->_tablePrimaryKey.' = ?';
		$rs = $this->_execute($sql, array($id));

		$sql  = 'DELETE ';
		$sql .= ' FROM '.$this->_tablesName['detailTable'];
		$sql .= ' WHERE '.$this->_joinFields['detailTable'].' = ?';
		$rs = $this->_execute($sql, array($id));

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



	function _insert()
	{
	}

	function _insertMain($values=NULL)
	{
		$values = $this->_collectFieldsValues();
		$tempFields 		= array();
		$tempInsertField 	= array();
		$queryParams 		= array();
		foreach ($this->_fieldsList as $k=>$v)
		{
			if ($v['type']==AR_TYPE_VIRTUAL || (isset($v['detailTable']) && $v['detailTable']===true)) continue;
			if (isset($v['defaultInsertValue']))
			{
				$values[$k] = $v['defaultInsertValue'];
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
			$queryParams[] = $values[$k];
			$tempFields[] = $k;
			$tempInsertField[] = '?';
		}
		$sql  = 'INSERT INTO '.$this->_tablesName['mainTable'].' ';
		$sql .= '('.implode(',', $tempFields).')';
		$sql .= ' VALUES ( '.implode(',', $tempInsertField).');';

		$this->_execute($sql, $queryParams);
		$this->setId($this->_conn->Insert_ID());
		return $this->getId();
	}

	function _insertDetail($values=NULL)
	{
		// TODO
		// forse il loadFromArray può essere spostato in save()
		$values[$this->_joinFields['detailTable']] = $this->getId();
		if (!is_null($values)) $this->loadFromArray($values);
		$values 			= $this->_collectFieldsValues();

		$tempFields 		= array();
		$tempInsertField 	= array();
		$queryParams 		= array();
		foreach ($this->_fieldsList as $k=>$v)
		{
			if ($v['type']==AR_TYPE_VIRTUAL || (!isset($v['detailTable']) || $v['detailTable']===false)) continue;
			if (isset($v['defaultInsertValue']) && ($values[$k]==$v['defaultValue'] || empty($values[$k])))
			{
				$values[$k] = $v['defaultInsertValue'];
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
			$queryParams[] = is_null($values[$k]) && !$v['primaryKey']==true ? '' : $values[$k];
			$tempFields[] = $k;
			$tempInsertField[] = '?';
		}


		$sql  = 'INSERT INTO '.$this->_tablesName['detailTable'].' ';
		$sql .= '('.implode(',', $tempFields).')';
		$sql .= ' VALUES ( '.implode(',', $tempInsertField).');';

		$this->_execute($sql, $queryParams);
		$this->setDetailId($this->_conn->Insert_ID());
		return $this->getDetailId();
	}


	function _update()
	{
	}

	function _updateMain($values=NULL)
	{
		$values = $this->_collectFieldsValues();
		$tempFields 		= array();
		$tempInsertField 	= array();
		$queryParams 		= array();
		foreach ($this->_fieldsList as $k=>$v)
		{
			if ($v['type']==AR_TYPE_VIRTUAL || (isset($v['detailTable']) && $v['detailTable']===true)) continue;
			if (isset($v['defaultUpdateValue']))
			{
				$values[$k] = $v['defaultUpdateValue'];

				// TODO
				// controllare il tipo del campo
				if ($values[$k]=='NOW()') $values[$k] = date('Y-m-d H:i:s');
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
			$queryParams[] = $values[$k];
			$tempFields[] = $k;
			$tempInsertField[] = '?';
		}
		$queryParams[] = $this->getId();

		$sql  = 'UPDATE '.$this->_tablesName['mainTable'].' SET ';
		$sql .= implode('= ?,', $tempFields).'= ? ';
		$sql .= ' WHERE '.$this->_tablePrimaryKey.' = ?';

		$rs = $this->_execute($sql, $queryParams);
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

	function _updateDetail($values=NULL)
	{
		// TODO
		// forse il loadFromArray può essere spostato in save()
		if (!is_null($values)) $this->loadFromArray($values);
		$values 			= $this->_collectFieldsValues();
		$tempFields 		= array();
		$tempInsertField 	= array();
		$queryParams 		= array();
		foreach ($this->_fieldsList as $k=>$v)
		{
			if ($v['type']==AR_TYPE_VIRTUAL || (!isset($v['detailTable']) || $v['detailTable']===false)) continue;
			if (isset($v['defaultUpdateValue']))
			{
				$values[$k] = $v['defaultUpdateValue'];

				if ($values[$k]=='NOW()' && ( $v['type']==AR_TYPE_DATE|| $v['type']==AR_TYPE_DATETIME ) )
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
			$queryParams[] = is_null($values[$k]) ? '' : $values[$k];
			$tempFields[] = $k;
			$tempInsertField[] = '?';
		}
		$queryParams[] = $this->getDetailId();

		$sql  = 'UPDATE '.$this->_tablesName['detailTable'].' SET ';
		$sql .= implode('= ?,', $tempFields).'= ? ';
		$sql .= ' WHERE '.$this->_tableDetailPrimaryKey.' = ?';

		$rs = $this->_execute($sql, $queryParams);
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

	// TODO
	// rimuovere la funzione perché è un duplicato di getDetailPrimaryKey
	function getDetailPrimaryKeyName()
	{
		return $this->_tableDetailPrimaryKey;
	}

	/*****/

	function _replaceSqlTag($sql)
	{
		$sql = str_replace('##TABLE_NAME##', 		$this->_tablesName['mainTable'], $sql);
		$sql = str_replace('##TABLE_DETAIL_NAME##', $this->_tablesName['detailTable'], $sql);
		return parent::_replaceSqlTag($sql);
	}

	function _getQuerySqlString($queryName, $replaceTags=true)
	{
		$queryName = strtolower($queryName);

		if (!array_key_exists('all', $this->_querySql))
		{
			$sql  = 'SELECT *';
			$sql .= ' FROM '.$this->_tablesName['mainTable'];
			$sql .= ' INNER JOIN '.$this->_tablesName['detailTable'].' ON ('.implode('=', $this->_joinFields).')';
			$this->addQuery('all', $sql);
		}

		return parent::_getQuerySqlString($queryName, $replaceTags);
	}

	function loadDictionary($field, $queryName=NULL, $skipEmpty=false, $delimiter='', $version='PUBLISHED')
	{
		$application = &org_glizy_ObjectValues::get('org.glizy', 'application');
		if (is_null($queryName))
		{
			$field = explode(',', $field);
			$filter = $this->_fieldsList[ count($field)>1 ? $field[1] : $field[0] ][ 'filter' ];
			$sql  = 'SELECT DISTINCT UPPER('.$field[0].') as k, '.(count($field)>1 ? $field[1] : $field[0]).' as v' ;
			$sql .= ' FROM '.$this->_tablesName['mainTable'];
			$sql .= ' INNER JOIN '.$this->_tablesName['detailTable'].' ON ('.implode('=', $this->_joinFields).')';
			$sql .= ' WHERE 1=1';
			foreach ($this->_fieldsList as $k=>$v)
			{
				if (isset($v['languageField']) && $v['languageField']===true)
				{
					$sql .= ' AND '.$k.'='.( method_exists($application, 'getEditingLanguageId') ? $application->getEditingLanguageId() : $application->getLanguageId() );
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
}