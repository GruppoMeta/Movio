<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizy_redis_Client extends GlizyObject
{
    /** @var PredisPhpdoc\Client $predis */
    private static $predis;
    private static $currentRedisDB = null;
    private $redisDB;

    function __construct($redisDB)
    {
        $this->redisDB = $redisDB;
    }

    public function __call($method, $args)
    {
        if (self::$currentRedisDB != $this->redisDB) {
            self::$predis->select($this->redisDB);
            self::$currentRedisDB = $this->redisDB;
        }
        return call_user_func_array([self::$predis, $method], $args);
    }

    /**
     * @param int $redisDB
     * @return \Predis\Client|\PredisPhpdoc\Client
     */
    public static function getConnection($redisDB = 0)
    {
        if (is_null(self::$predis)) {
            $host = __Config::get('glizy.database.caching.redis');
            self::$predis = new Predis\Client($host ? $host : 'tcp://127.0.0.1:6379');
        }

        return new self($redisDB);
    }
}