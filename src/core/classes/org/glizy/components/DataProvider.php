<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_components_DataProvider extends org_glizy_components_Component
{
    var $_classPath;
    var $_activeRecord;
    var $_recordIterator;
    private $recordsCount;

    /**
     * Init
     *
     * @return    void
     * @access    public
     */
    function init()
    {
        $this->defineAttribute('recordClassName',    true,     '',        COMPONENT_TYPE_STRING);
        $this->defineAttribute('query',             false,    'All',    COMPONENT_TYPE_STRING);
        $this->defineAttribute('order',             false,    '',        COMPONENT_TYPE_STRING);
        $this->defineAttribute('orderModifier',     false,    'ASC',        COMPONENT_TYPE_STRING);
        $this->defineAttribute('limit',             false,    '',        COMPONENT_TYPE_STRING);
        $this->defineAttribute('filters',             false,    '',        COMPONENT_TYPE_STRING);
        $this->defineAttribute('categories',         false,    '',        COMPONENT_TYPE_STRING);
        $this->defineAttribute('params',             false,    '',        COMPONENT_TYPE_OBJECT);
        $this->defineAttribute('checkIntegrity',     false,    true,        COMPONENT_TYPE_BOOLEAN);
        $this->defineAttribute('queryOperator',     false, 'AND',     COMPONENT_TYPE_STRING);
        $this->defineAttribute('showAll',     false, false,     COMPONENT_TYPE_BOOLEAN);
        $this->defineAttribute('filterClass',   false, '',     COMPONENT_TYPE_STRING);

        parent::init();
    }


    /**
     * Process
     *
     * @return    boolean    false if the process is aborted
     * @access    public
     */
    function process()
    {
        $this->_classPath     = $this->getAttribute('recordClassName');
        if (is_null($this->_classPath))
        {
            // TODO
            // visualizzare FATAL ERROR
            $this->RaiseError("DataProvider: record class don't found",__FUNCTION__,__FILE__,__LINE__,'verbosedie');
        }
        else
        {
            $this->_recordIterator = &org_glizy_ObjectFactory::createModelIterator($this->_classPath);
            $this->_activeRecord = &org_glizy_ObjectFactory::createModel($this->_classPath);

            if ($this->getAttribute('showAll') && method_exists($it, 'showAll')) {
                $this->_recordIterator->showAll();
            }
            /*
            $this->_activeRecord = &org_glizy_ObjectFactory::createModel($this->_classPath);
            if ($this->_activeRecord===false)
            {
                // TODO
                // visualizzare FATAL ERROR
                $this->RaiseError("DataProvider: record class don't found",__FUNCTION__,__FILE__,__LINE__,'verbosedie');
            }
            $this->_activeRecord->setDefaultQuery( $this->getAttribute('query') );
            */
        }

        $this->processChilds();
    }


    /**
     * Render
     *
     * @return    void
     * @access    public
     */
    function render()
    {
    }

    function &loadQuery($queryName='', $options=array())
    {
        if (is_null($this->_recordIterator)) {
            $this->_recordIterator = &org_glizy_ObjectFactory::createModelIterator($this->getAttribute('recordClassName'));

            if ($this->getAttribute('showAll') && method_exists($it, 'showAll')) {
                $this->_recordIterator->showAll();
            }
        }

        if (empty($queryName))
        {
            $queryName = $this->getAttribute('query');
        }

        $order = $this->getAttribute('order');
        if (!empty($order))
        {
            $order = explode(',', $order);
            $orderModifier = $this->getAttribute('orderModifier');
            $options['order'] = array();
            foreach($order as $v) {
                list($field, $dir) = explode(' ', $v);
                $options['order'][$field] = $dir ? :$orderModifier;
            }
        }

		if ($this->getAttribute('useQueryParams') && isset($options['filters'])) {
            $options['params'] = array();

            if (count($options['filters'])) {
                foreach($options['filters'] as $k=>$v) {
                    $options['params'][$k] = is_array($v) ? $v[1] : $v;
                }
            }
            unset($options['filters']);
        }

        if ($this->getAttribute('limit')) $options['limit'] = explode(',', $this->getAttribute('limit'));
        if ($this->getAttribute('filters')) $options['filters'] = $this->getAttribute('filters');

        $filterClassName = $this->getAttribute('filterClass');
        $filterClass = $filterClassName ? org_glizy_ObjectFactory::createObject($filterClassName) : null;
        if ($filterClass) {
            if ($filterClass instanceof org_glizy_components_interfaces_IDataProviderFilter) {
                $options = $filterClass->getFilters($queryName, $options);
            } else {
                throw org_glizy_exceptions_InterfaceException::notImplemented('org.glizy.components.interfaces.IDataProviderFilter', $filterClassName);
            }
        }


        // TODO
        // if ($this->getAttribute('categories')) $options['categories'] = $this->getAttribute('categories');
        // TODO
        // if ($this->getAttribute('params')) $options['params'] = $this->getAttribute('params');

        $it = $this->_recordIterator->load($queryName);

        if (!empty($options['filters'])) {
            if ($this->getAttribute('queryOperator') === 'OR') {
                $it->setOrFilters($options['filters']);
            }
            else {
                $it->setFilters($options['filters']);
            }
        }

        if (isset($options['order'])) {
            $it->setOrderBy($options['order']);
        }

        if (isset($options['limit'])) {
            $it->limit($options['limit']);
        }

        $this->recordsCount = $it->count();
        return $it;
    }

    function &load($id)
    {
        $queryName = strtolower($this->getAttribute('query'));
        if ($queryName && $queryName!=='all') {
            $it = &org_glizy_ObjectFactory::createModelIterator($this->getAttribute('recordClassName'));
            $it->load($queryName, array('id' => $id));
            $this->_activeRecord = $it->first();
        } else {
            $this->_activeRecord->load($id);
        }
        return $this->getObject();
    }

    /**
     * @return org_glizy_dataAccess_ActiveRecord
     */
    function &getObject()
    {
        return $this->_activeRecord;
    }

    function &getNewObject()
    {
        $ar = &org_glizy_ObjectFactory::createModel($this->_classPath);
        //$ar->setDefaultQuery( $this->getAttribute('query') );
        return $ar;
    }

    function getRecordClassName()
    {
        return $this->getAttribute('recordClassName');
    }

    function getItems($name, $bindToField=NULL)
    {
        if (!is_null($bindToField))
        {
            $name = $bindToField;
        }

        $result = array();
        $iterator = &$this->loadQuery();
        foreach ($iterator as $ar) {
            $result[] = array('key' => $ar->getId(), 'value' => $ar->$name);
        }

        return $result;
    }

    function getLastSql()
    {
        return $this->_activeRecord->lastSql;
    }

    function getRecordsCount()
    {
        return $this->recordsCount;
    }
}