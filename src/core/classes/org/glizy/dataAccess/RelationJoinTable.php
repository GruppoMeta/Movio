<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


/** class org_glizy_dataAccess_RelationJoinTable */
class org_glizy_dataAccess_RelationJoinTable extends org_glizy_dataAccess_RelationMany2Many
{
	var $_objectName = '';
	var $_objectField = 'join_objectName';
	var $_ordered = true;
	var $_newRecord;

	function __construct(&$parent, $options)
	{
		parent::__construct($parent, $options);
		$this->_objectName = $options['objectName'];
	}

	function build($params=array())
	{
		$this->record 	= org_glizy_ObjectFactory::createModel($this->_className);
		$this->iterator	= NULL;

		$parentId = $this->_parent->getId();
		if (!is_null($parentId))
		{
			$this->_newRecord = empty( $parentId );
			$this->iterator	= &$this->record->loadQuery('All', array('filters' => array($this->_key => 	$parentId, $this->_objectField => array( '=', $this->_objectName ) ),
																	 'order' => $this->record->getPrimaryKey() ) );

			if (is_null($this->_parent->{$this->_bindTo}) )
			{
				$this->record = NULL;
				$values = array();
				while($this->iterator->hasMore())
				{
					$this->record = &$this->iterator->current();
					$values[] = $this->record->{$this->_destinationKey};
					$this->iterator->next();
				}
				$this->_parent->{$this->_bindTo} = implode(',', $values);
			}
			else
			{
				$this->record = &$this->iterator->current();
			}

			if ($this->iterator->count())
			{
				$this->iterator->first();
			}
		}
	}

	function postSave()
	{
		$values = $this->_parent->{$this->_bindTo};
		if (is_null($values)) return;
		$values = is_string($values) ?
										!empty($values) ? explode(',', $values) : array()
										:
										$values;
		if ( !is_array( $values ) )
		{
			$values = array( $values );
		}
		if (is_null($this->record->{$this->_destinationKey})
			|| $this->record->{$this->_destinationKey} == $this->record->getFieldDefaultValue($this->_destinationKey)
			|| is_null($this->iterator))
		{
			// nuovo record
			$parentId = $this->_parent->getId();
			foreach ($values as $v)
			{
				$this->record = & org_glizy_ObjectFactory::createModel($this->_className);
				$this->record->{$this->_key} = $this->_parent->getId();
				$this->record->{$this->_destinationKey} = $v;
				$this->record->{$this->_objectField} = $this->_objectName;
				$newId = $this->record->save();
			}
		}
		else
		{
			$recordIds = array();
			while($this->iterator->hasMore())
			{
				$ar = &$this->iterator->current();

				if ( $this->_ordered )
				{
					if ( !$this->_newRecord )
					{
						$ar->delete();
					}
				}
				else
				{
					if (!in_array($ar->{$this->_destinationKey}, $values))
					{
						$ar->delete();
					}
					else
					{
						$recordIds[] = $ar->{$this->_destinationKey};
					}
				}
				$this->iterator->next();
			}
			if (count($values))
			{
				foreach ($values as $v)
				{
					if (!in_array($v, $recordIds))
					{
						$this->record = & org_glizy_ObjectFactory::createModel($this->_className);
						$this->record->{$this->_key} = $this->_parent->getId();
						$this->record->{$this->_destinationKey} = $v;
						$this->record->{$this->_objectField} = $this->_objectName;
						$newId = $this->record->save();
					}
				}
			}
		}
	}
}