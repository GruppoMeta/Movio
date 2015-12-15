<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

/** class org_glizy_dataAccess_RelationHasOne */
class org_glizy_dataAccess_RelationHasOne extends org_glizy_dataAccess_Relation
{

	function __construct(&$parent, $options)
	{
		parent::__construct($parent, $options);
		assert(isset($options['field']));
		$this->_key = $options['field'];
		$this->_destinationKey = isset($options['destinationField']) ? $options['destinationField'] : NULL;
	}

	function build($params=array())
	{
		$this->record = org_glizy_ObjectFactory::createModel($this->_className);
		$this->record->setProcessRelations(false);
		$this->_getDestinationField();
		$this->record->{$this->_destinationKey} = $this->_parent->{$this->_key};

		if (count($params))
		{
			$this->record->loadFromArray($params);
		}
		$this->record->find();
		$this->current();
	}

	function create($params)
	{
		$this->_reset();
		$this->record = & org_glizy_ObjectFactory::createModel($this->_className);
		$this->record->setProcessRelations(false);
		$this->_getDestinationField();

		if (count($params))
		{
			$this->record->loadFromArray($params);
		}

		$this->_bindRecordFields();
	}

	function bind(&$object)
	{
		if (get_class($object)!=$this->_className)
		{
			// TODO
			// visualizzare errore
		}

		$this->_reset();
		$this->record = &$object;
		$this->record->setProcessRelations(false);
		$this->_getDestinationField();
		$this->_bindRecordFields();
	}

	function preSave()
	{
		$canSave = false;

		$currentValues = $this->record->getValuesAsArray();
		foreach ($currentValues as $k=>$v)
		{
			if (!array_key_exists($k, $this->_backUpValues) || $this->_backUpValues[$k]!=$v)
			{
				$canSave = true;
				break;
			}
		}
		if ($canSave)
		{
			$this->record->save();
			$this->_backUpValues = $this->record->getValuesAsArray();
			$this->_parent->{$this->_key} = $this->record->{$this->_destinationKey};
		}
	}

	function _getDestinationField()
	{
		if (is_null($this->_destinationKey))
		{
			$this->_destinationKey = $this->record->getPrimaryKey();
			assert(!is_null($this->_destinationKey));
		}
	}

	function &current()
	{
		$this->_backUpValues = $this->record->getValuesAsArray();
		$this->_bindRecordFields();
		return $this->record;
	}

	function _reset()
	{
		$this->_backUpValues	= array();
	}

	function collectFieldsValues( $getVirtualField, $encode )
	{
		if (is_object($this->record))
		{
			return $this->record->_collectFieldsValues(false, $getVirtualField, $encode);
		}
	}
}