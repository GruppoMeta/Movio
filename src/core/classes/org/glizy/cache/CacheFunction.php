<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */



class org_glizy_cache_CacheFunction extends GlizyObject
{
    private $_cacheObj;
    private $parent;
    private $memoryCache;
    private $group;
    private static $memArray = array();


    function __construct($parent, $lifeTime=null, $memoryCache=false, $cacheFolder=null, $group=null)
    {
        $cacheFolder = $cacheFolder ? $cacheFolder : org_glizy_Paths::getRealPath('CACHE_CODE');
        $this->parent = $parent;
        $this->memoryCache = $memoryCache;
        $this->group = $group ? $group : $cacheFolder.get_class($this->parent);
        $options = array(
            'cacheDir' => $cacheFolder,
            'lifeTime' => !$lifeTime ? org_glizy_Config::get('CACHE_CODE') : $lifeTime,
            'readControlType' => '',
            'fileExtension' => '.php'
        );

        if ($options['lifeTime']=='-1') {
            $options['lifeTime'] = null;
        }

        $this->_cacheObj = &org_glizy_ObjectFactory::createObject('org.glizy.cache.CacheFile', $options);
    }

    public function get($method, $args, $lambda)
    {
        $fileName = $method.serialize($args);
        $memId = $fileName.$this->group;
        $data = $this->getMemoryCache($memId);

        if ( $data !== false) {
            return $data;
        } else  {
            $data = $this->_cacheObj->get($fileName, $this->group);
            if ($data===false) {
               $data = $lambda();
               $this->_cacheObj->save($this->serialize($data), $fileName, $this->group);
            } else {
                $data = $this->unserialize($data);
            }

            $this->setMemoryCache($memId, $data);
            return $data;
        }
    }

    public function invalidateGroup()
    {
        $this->_cacheObj->clean($this->group);
    }

    public function remove($method, $args)
    {
        $fileName = $method.serialize($args);
        $this->_cacheObj->remove($fileName, $this->group);
    }

    private function getMemoryCache($id)
    {
        if ($this->memoryCache && isset(self::$memArray[$id])) {
            return self::$memArray[$id];
        }
        return false;
    }

    private function setMemoryCache($id, $data)
    {
        if ($this->memoryCache) {
            self::$memArray[$id] = $data;
        }
    }

    private function serialize($data)
    {
        return is_string($data) ? 'N|'.$data : 'Y|'.serialize($data);
    }

    private function unserialize($data)
    {
        return substr($data, 0, 2) == 'N|' ? substr($data, 2) : unserialize(substr($data, 2));
    }
}
