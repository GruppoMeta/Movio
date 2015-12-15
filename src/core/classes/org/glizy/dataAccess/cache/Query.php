<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizy_dataAccess_cache_Query extends GlizyObject
{
    private $_cacheObj;
    private $model;
    private $group;


    function __construct($model, $lifeTime=null, $cacheFolder=null)
    {
        $cacheFolder = $cacheFolder ? $cacheFolder : org_glizy_Paths::getRealPath('CACHE_CODE');
        $this->model = $model;
        $this->group = $cacheFolder.$model;
        $options = array(
            'cacheDir' => $cacheFolder,
            'lifeTime' => !$lifeTime ? org_glizy_Config::get('CACHE_CODE') : $lifeTime,
            'readControlType' => '',
            'fileExtension' => '.php'
        );

        $this->_cacheObj = &org_glizy_ObjectFactory::createObject('org.glizy.cache.CacheFile', $options);
    }

    public function get($queryName, $options=array())
    {
        $fileName = $queryName.serialize($options);
        return $this->getWithName($queryName.serialize($options), $queryName, $options );
    }

    public function getWithName($fileName, $queryName, $options=array())
    {
        $data = $this->_cacheObj->get($fileName, $this->group);
        if ($data===false) {
            $data = array();
            $it = org_glizy_ObjectFactory::createModelIterator($this->model, $queryName, $options);
            foreach ($it as $ar) {
                $data[] = $ar->getValuesAsArray();
            }
           $this->_cacheObj->save($this->serialize($data), $fileName, $this->group);
        } else {
            $data = $this->unserialize($data);
        }

        return new org_glizy_dataAccess_cache_Iterator($data);
    }

    public function remove($queryName, $args=array())
    {
        $this->removeWithName($queryName.serialize($args));
    }

    public function removeWithName($fileName)
    {
        $this->_cacheObj->remove($fileName, $this->group);
    }

    private function serialize($data)
    {
        return serialize($data);
    }

    private function unserialize($data)
    {
        return unserialize($data);
    }
}