<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


/** class org_glizy_dataAccess_RelationHasMany */
class org_glizy_dataAccess_RelationHasMany extends org_glizy_dataAccess_Relation
{
	var $recordSet		= NULL;
	var $iterator		= NULL;
	var $_recordsList	= array();

	function  __construct(&$parent, $options)
	{
		parent::__construct($parent, $options);
		assert(isset($options['field']));
		$this->_key = $options['field'];
		assert(!is_null($this->_destinationKey));
		$this->_destinationKey = $options['destinationField'];
	}

	function build($params=array())
	{
		$this->record 	= &org_glizy_ObjectFactory::createModel($this->_className);
		$filters = array();

		if ( strpos( $this->_parent->{$this->_key}, ',' ) !== false )
		{
			$filters[ $this->_destinationKey ] = array( 'IN ('.$this->_parent->{$this->_key}.')' );
		}
		else
		{
			$filters[ $this->_destinationKey ] = $this->_parent->{$this->_key};
		}
		$this->iterator	= &$this->record->loadQuery('All', array('filters' => $filters ) );

		if ($this->iterator->count())
		{
			$this->current();
		}
		else
		{
			$this->record = NULL;
		}
	}

	function create($params)
	{
		$this->_reset();
		$this->record = & org_glizy_ObjectFactory::createModel($this->_className);
		$this->record->setProcessRelations(false);

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
		$this->record->{$this->_destinationKey} 		= $this->_parent->{$this->_key};
		$this->_recordsList[$this->record->getId()] 	= &$this->record;
		$this->_backUpValues[$this->record->getId()] 	= $this->record->getValuesAsArray();
		$this->record->setProcessRelations(false);
		$this->_bindRecordFields();
	}

	function postSave()
	{
		if (!is_object($this->record)) return;
		if (is_null($this->record->{$this->_destinationKey}))
		{
			// nuovo record
			$this->record->{$this->_destinationKey} = $this->_parent->{$this->_key};
			$newId = $this->record->save();
			$this->_backUpValues[$newId] = $this->record->getValuesAsArray();
		}
		else
		{
			foreach($this->_recordsList as $id=>$obj)
			{
				$canSave = false;
				$currentValues = $this->_recordsList[$id]->getValuesAsArray();
				foreach ($currentValues as $k=>$v)
				{
					if (!array_key_exists($k, $this->_backUpValues[$this->_recordsList[$id]->getId()]) || $this->_backUpValues[$this->_recordsList[$id]->getId()][$k]!=$v)
					{
						$canSave = true;
						break;
					}
				}

				if ($canSave)
				{
					$this->_recordsList[$id]->save();
					$this->_backUpValues[$this->_recordsList[$id]->getId()] = $this->_recordsList[$id]->getValuesAsArray();
				}
			}
		}
	}

	function &getIterator()
	{
		return $this->iterator;
	}

	/* implementa gli stessi metodi dell'iterator */
	function rewind()
	{
		assert(!is_null($this->iterator));
        $this->iterator->rewind();
		$this->current();
   }

	function valid()
	{
		assert(!is_null($this->iterator));
        return $this->iterator->valid();
    }

	function &first()
	{
		assert(!is_null($this->iterator));
        $this->iterator->first();
   		$this->current();
		return $this->record;
	}

    function key()
	{
		assert(!is_null($this->iterator));
        return $this->iterator->key();
    }

    function next()
	{
		assert(!is_null($this->iterator));

		$this->iterator->next();
		if ($this->iterator->hasMore())
		{
			$this->current();
		}
    }

	function count()
	{
		assert(!is_null($this->iterator));
		return $this->iterator->count();
	}

	function hasMore()
	{
		assert(!is_null($this->iterator));
		return $this->iterator->hasMore();
	}

	function &current()
	{
		assert(!is_null($this->iterator));
		$this->record = &$this->iterator->current();
		$this->_recordsList[$this->record->getId()] = &$this->record;
		$this->_backUpValues[$this->record->getId()] = $this->record->getValuesAsArray();
		$this->_bindRecordFields();
		return $this->record;
	}

	function _reset()
	{
		$this->record			= NULL;
		$this->iterator			= NULL;
		$this->_recordsList		= array();
		$this->_backUpValues	= array();
	}

	function collectFieldsValues( $getVirtualField, $encode )
	{
		$output = array();
		while ( $this->hasMore() )
		{
			$output[] = $this->record->_collectFieldsValues(false, $getVirtualField, $encode);
			$this->next();
		}
		return $output;
	}
}