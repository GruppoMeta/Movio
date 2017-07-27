<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


require_once(GLZ_LIBS_DIR."Cache_Lite/Lite.php");
/**
 * Class org_glizy_cache_CacheFile
 */
class org_glizy_cache_CacheFile extends Cache_Lite
{
	var $_fileExtension = '';

    /**
     * @param array $options
     */
	function __construct($options = array(NULL))
    {
		$options['readControl'] = false;
		parent::Cache_Lite($options);
		if (isset($options['fileExtension'])) $this->_fileExtension = $options['fileExtension'];
	}

    /**
     * @param        $id
     * @param string $group
     * @param bool   $doNotTestCacheValidity
     * @param null   $originalFile
     *
     * @return bool|string
     */
	function verify($id, $group = 'default', $doNotTestCacheValidity = false, $originalFile = NULL)
    {
        $this->_id = $id;
        $this->_group = $group;
        $data = false;
        if ($this->_caching)
		{
			$this->_setRefreshTime();
			$this->_setFileName($id, $group, $originalFile);
			clearstatcache();
			if (!is_null($originalFile)) $this->_id = $originalFile;

			if ($this->_lifeTime==-1)
			{
				if ((file_exists($this->_file)) && (@filemtime($this->_file) == @filemtime($this->_id)))
				{
					$data = $this->_file;
	            }
			}
			else
			{
	            if (($doNotTestCacheValidity) || (is_null($this->_refreshTime)))
				{
	                if (file_exists($this->_file)) {
	                    $data = $this->_file;
	                }
	            }
				else
				{
	                if ((file_exists($this->_file)) && (@filemtime($this->_file) > $this->_refreshTime))
					{
	                    $data = $this->_file;
	                }
	            }
			}
        }
		if (!is_null($originalFile)) $this->_id = $id;
        return $data;
    }

    /**
     * @return string
     */
	function getFileName()
	{
		return $this->_file;
	}

    /**
     * @param string $id
     * @param string $group
     */
	function _setFileName($id, $group)
    {
        if ($this->_fileNameProtection) {
            $suffix = 'cache_'.md5($group).'_'.md5($id).$this->_fileExtension;
        } else {
            $suffix = 'cache_'.$group.'_'.$id.$this->_fileExtension;
        }
        $root = $this->_cacheDir;
        if ($this->_hashedDirectoryLevel>0) {
            $hash = md5($suffix);
            for ($i=0 ; $i<$this->_hashedDirectoryLevel ; $i++) {
                $root = $root . 'cache_' . substr($hash, 0, $i + 1) . '/';
            }
        }
        $this->_fileName = $suffix;
        $this->_file = $root.$suffix;
    }

    /**
     * @param string $data
     * @param null   $id
     * @param string $group
     *
     * @return bool
     */
	function save($data, $id = NULL, $group = 'default')
	{
		$r = parent::save($data, $id, $group);
		if ($this->_lifeTime==-1)
		{
			@touch($this->_file, filemtime($this->_id));
		}
		@chmod( $this->_file, 0777 );
		return $r;
	}

    /**
     * @param string $file
     *
     * @return bool
     */
    function _unlink($file)
    {
        @unlink($file);
        return true;
    }

    /**
     * @param null $cacheCode
     */
	static function cleanPHP($cacheCode=null)
	{
        $cacheCode = $cacheCode == null ? __Paths::get( 'CACHE_CODE' ) : $cacheCode;
		self::rm( $cacheCode.'*.php' );
        if (function_exists('opcache_get_status')) {
            opcache_reset();
        }
        $evt = array('type' => GLZ_EVT_CACHE_CLEAN, 'data' => $cacheCode);
        $evtObject = org_glizy_ObjectFactory::createObject('org.glizy.events.Event', null, $evt);
        org_glizy_events_EventDispatcher::dispatchEvent($evtObject);
    }

    /**
     * @param $path
     */
	static function rm( $path )
	{
		foreach (glob( $path ) as $filename)
		{
		   @unlink($filename);
		}
	}
}