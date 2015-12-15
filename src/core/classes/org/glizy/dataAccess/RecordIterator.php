<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

/** class  org_glizy_dataAccess_RecordIterator */
class org_glizy_dataAccess_RecordIterator extends GlizyObject implements Iterator
{
	var $_rs 				= NULL;
	var $_recordClassName 	= '';
	var $_count = NULL;

	function __construct(&$rs, $recordClassName)
	{
		$this->_rs 				= &$rs;
		$this->_recordClassName = $recordClassName;
	}

	function rewind()
	{
        $this->_rs->MoveFirst();
    }

	function valid()
	{
        return !$this->_rs->EOF;
    }

	function &first()
	{
        $this->_rs->MoveFirst();
		return $this->current();
    }

    function key()
	{
        return $this->_rs->recordPos();
    }

    function &current()
	{
        $fields = $this->_rs->fields;
		$activeRecord = & org_glizy_ObjectFactory::createModel($this->_recordClassName);
		$activeRecord->loadFromArray($fields);
		return $activeRecord;
    }

    function next()
	{
        $this->_rs->MoveNext();
    }

	function count()
	{
		return $this->_count == NULL ? $this->_rs->RecordCount() : $this->_count;
	}

	function setCount( $v )
	{
		$this->_count = $v;
	}

	function hasMore()
	{
		return !$this->_rs->EOF;
	}

	function recordPos()
	{
		return $this->_rs->recordPos();
	}
}