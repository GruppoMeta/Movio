<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

/** class org_glizy_dataAccess_Relation */
class org_glizy_dataAccess_Relation extends GlizyObject
{
	var $_key 			= '';
	var $_destinationKey = '';
	var $_parent		= NULL;
	var $_conn			= NULL;
	var $_className 	= '';
	var $record			= NULL;
	var $_backUpValues 	= array();

	function __construct(&$parent, $options)
	{
		//parent::BaseClass();
		$this->_conn 		= &org_glizy_dataAccess_DataAccess::getConnection();
		$this->_parent 		= &$parent;
		assert(isset($options['className']));
		$this->_className	= $options['className'];
	}

	function createObject(&$parent, $options)
	{
		$relation = NULL;
		switch (strtolower($options['type']))
		{
			case 'hasone':
			case 'has_one':
			case 'fk':
				$relation = new org_glizy_dataAccess_RelationHasOne($parent, $options);
				break;
			case 'hasmany':
			case 'has_many':
				$relation = new org_glizy_dataAccess_RelationHasMany($parent, $options);
				break;
			case 'many':
				$relation = new org_glizy_dataAccess_RelationMany2Many($parent, $options);
				break;
			case 'jointable':
				$relation = new org_glizy_dataAccess_RelationJoinTable($parent, $options);
				break;
			case 'contenttable':
				$relation = new org_glizy_dataAccess_RelationContentTable($parent, $options);
				break;
			default:
				// TODO
				// visualizzare errore
				break;
		}

		return $relation;
	}

	function &getObject()
	{
		return $this->record;
	}

	function _bindRecordFields()
	{
		assert(!is_null($this->record));

		foreach (array_keys($this->record->_fieldsList) as $fieldName)
		{
			$this->$fieldName = &$this->record->$fieldName;
		}
	}


	// TODO
	// implementare i metodi astratti

	function preSave()
	{

	}

	function postSave()
	{
	}

	function delete()
	{
	}
}