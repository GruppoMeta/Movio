<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


/** class org_glizy_dataAccess_RelationMany2Many */
class org_glizy_dataAccess_RelationMany2Many extends org_glizy_dataAccess_Relation
{
	var $recordSet		= NULL;
	var $iterator		= NULL;
	var $_bindTo		= '';

	function __construct(&$parent, $options)
	{
		parent::__construct($parent, $options);
		assert(isset($options['field']));
		$this->_key = $options['field'];
		assert(!is_null($this->_destinationKey));
		$this->_destinationKey = $options['destinationField'];
		$this->_bindTo = $options['bindTo'];
	}

	function build($params=array())
	{
		$this->record 	= org_glizy_ObjectFactory::createModel($this->_className);
		$this->iterator	= NULL;

		$parentId = $this->_parent->getId();
		if (!is_null($parentId))
		{
			$this->iterator	= &$this->record->loadQuery('All', array('filters' => array($this->_key => 	$parentId)));

			// TODO
			// 28 mag 2006
			// controllo se il campo linkato è già riempito
			// in questo caso non carico i valori
			// ho fatto in questo modo perché se si sava un valore
			// proveniente da un post con i valori riempiti
			// questi venivano cancellati
			// la soluzione non è l'ideale
			// c'è da trovare un modo migliore per risolvere il problema
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
				$this->rewind();
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
		//$this->_recordsList[$this->record->getId()] 	= &$this->record;
		//$this->_backUpValues[$this->record->getId()] 	= $this->record->getValuesAsArray();
		$this->record->setProcessRelations(false);
		$this->_bindRecordFields();
	}

	function postSave()
	{
		$values = $this->_parent->{$this->_bindTo};
		if (is_null($values)) return;
		$values = is_string($values) ?
										!empty($values) ? explode(',', $values) : array()
										:
										$values;

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
				$newId = $this->record->save();
			}
		}
		else
		{
			$recordIds = array();
			while($this->iterator->hasMore())
			{
				$ar = &$this->iterator->current();
				if (!in_array($ar->{$this->_destinationKey}, $values))
				{
					$ar->delete();
				}
				else
				{
					$recordIds[] = $ar->{$this->_destinationKey};
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
						$newId = $this->record->save();
					}
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
		$this->_bindRecordFields();
		return $this->record;
	}

	function _reset()
	{
		$this->record			= NULL;
		$this->iterator			= NULL;
		//$this->_recordsList		= array();
		//$this->_backUpValues	= array();
	}
}