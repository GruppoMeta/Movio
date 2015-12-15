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

    /**
     * @return \Predis\Client|\PredisPhpdoc\Client
     */
    public static function getConnection()
    {
        if (is_null(self::$predis)) {
            $host = __Config::get('glizy.database.caching.redis');

            self::$predis = new Predis\Client($host ? $host : 'tcp://127.0.0.1:6379');
        }

        return self::$predis;
    }
}