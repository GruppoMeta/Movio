<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_components_DataDictionary extends org_glizy_components_Component
{
	var $iterator;

	/**
	 * Init
	 *
	 * @return	void
	 * @access	public
	 */
	function init()
	{
		$this->defineAttribute('recordClassName',	true, 	'',		COMPONENT_TYPE_STRING);
		$this->defineAttribute('query', 			false,	NULL,	COMPONENT_TYPE_STRING);
	    $this->defineAttribute('queryParams', 		false,	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('field', 			false,	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('skipEmpty', 		false,	true,	COMPONENT_TYPE_BOOLEAN);
        $this->defineAttribute('delimiter',         false,  '', COMPONENT_TYPE_STRING);
		$this->defineAttribute('useCache', 		    false,	false,	COMPONENT_TYPE_BOOLEAN);
		parent::init();
	}


	/**
	 * Process
	 *
	 * @return	boolean	false if the process is aborted
	 * @access	public
	 */
	function process()
	{
		$classPath 	= $this->getAttribute('recordClassName');
		if (is_null($classPath))
		{
			// TODO
			// visualizzare FATAL ERROR
			$this->RaiseError("DataProvider: record class don't found",__FUNCTION__,__FILE__,__LINE__,'verbosedie');
		}
		else
		{
			$this->iterator = &org_glizy_ObjectFactory::createModelIterator($classPath);
			if ($this->iterator === false)
			{
				// TODO
				// visualizzare FATAL ERROR
				$this->RaiseError("DataProvider: record class don't found",__FUNCTION__,__FILE__,__LINE__,'verbosedie');
			}
		}
	}

	function getItems()
	{
        $oldCacheValue = __Config::get('QUERY_CACHING');
        __Config::set('QUERY_CACHING', $this->getAttribute('useCache'));

		if ( is_null( $this->iterator ) )
		{
			$this->process();
		}
		$items = org_glizy_ObjectValues::get('org.glizy.components.DataDictionary', $this->getAttribute('recordClassName').'.'.$this->getAttribute('field').$this->getAttribute('query'));
		if (is_null($items))
		{
			$items = __Session::get($this->getAttribute('recordClassName').'.'.$this->getAttribute('field').$this->getAttribute('query') );
		}

		if (is_null($items) )
		{
			$items = $this->loadDictionary(	$this->getAttribute('field'),
											$this->getAttribute('query'),
                                            unserialize($this->getAttribute('queryParams')),
											$this->getAttribute('skipEmpty'),
											$this->getAttribute('delimiter') );
    		org_glizy_ObjectValues::set('org.glizy.components.DataDictionary', $this->getAttribute('recordClassName').'.'.$this->getAttribute('field').$this->getAttribute('query'), $items);
			if ( $this->getAttribute('delimiter') != '' )
			{
				__Session::set($this->getAttribute('recordClassName').'.'.$this->getAttribute('field').$this->getAttribute('query'), $items);
			}
		}

        __Config::set('QUERY_CACHING', $oldCacheValue);
		return $items;
	}

    function loadDictionary($field, $queryName = null, $queryParams = null, $skipEmpty = false, $delimiter = '')
    {
        if ($queryName) {
            $this->iterator->load($queryName, $queryParams);
            $k = 'k';
            $v = 'v';
        }
        else {
            $field = explode(',', $field);

            if (count($field) == 1) {
                $k = $field[0];
                $v = $field[0];
            } else {
                $k = $field[0];
                $v = $field[1];
            }

            if (method_exists($this->iterator, 'selectDistinct')) {
                $this->iterator->selectDistinct($v);
            }

            $this->iterator->orderBy($v);
        }

        $result = array();
        $usedKeys = array();

        foreach ($this->iterator as $ar) {
            $key = $ar->$k;
            $value = $ar->$v;

            if ($skipEmpty && empty($value)) {
                continue;
            }

            if (!$delimiter) {
                $result[] = array('key' => $key, 'value' => $value);
            } else {
                $kk = explode( $delimiter, $key );
				$vv = explode( $delimiter, $value );
				$l = count( $kk );
				for( $i = 0; $i < $l; $i++ ) {
					if ( !in_array( $kk[ $i ], $usedKeys ) )
					{
					 	$usedKeys[] = $kk[ $i ];
					 	$result[] = array('key' => $kk[ $i ], 'value' => $vv[ $i ] );
					}
				}
            }
        }

        if ($delimiter) {
			org_glizy_helpers_Array::arrayMultisortByLabel( $result, 'value' );
		}

        return $result;
	}
}