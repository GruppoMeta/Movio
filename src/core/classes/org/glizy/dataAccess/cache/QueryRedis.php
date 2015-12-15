<?php

    /**
     * This file is part of the GLIZY framework.
     * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
     * For the full copyright and license information, please view the COPYRIGHT.txt
     * file that was distributed with this source code.
     */
    class org_glizy_dataAccess_cache_QueryRedis extends GlizyObject {

        private $model;
        private $lifeTime;
        /** @var \Predis\Client $redis */
        protected $redis;

        /**
         * @param      $model
         * @param      $lifeTime
         * @param null $cacheFolder
         */
        function __construct($model, $lifeTime = -1, $cacheFolder = null) {
            $this->model    = $model;
            $this->lifeTime = $lifeTime;
            $this->redis    = org_glizy_redis_Client::getConnection();
        }

        /**
         * @param       $queryName
         * @param array $options
         *
         * @return org_glizy_dataAccess_cache_Iterator
         */
        public function get($queryName, $options = array()) {
            return $this->getWithName($queryName . serialize($options), $queryName, $options);
        }

        /**
         * @param       $key
         * @param       $queryName
         * @param array $options
         *
         * @return org_glizy_dataAccess_cache_Iterator
         */
        public function getWithName($key, $queryName, $options = array()) {
            $data = $this->redis->get($key);

            if ($data == null) {
                $data = array();
                /** @var org_glizy_dataAccess_cache_Iterator $it */
                $it = org_glizy_ObjectFactory::createModelIterator($this->model, $queryName, $options);
                /** @var org_glizy_dataAccess_cache_ActiveRecord $ar */
                foreach ($it as $ar) {
                    $data[] = $ar->getValuesAsArray();
                }
                $this->redis->set($key, $this->serialize($data));
            } else {
                $data = $this->unserialize($data);
            }

            if ($this->lifeTime != -1) {
                $this->redis->expire($key, $this->lifeTime);
            }

            return new org_glizy_dataAccess_cache_Iterator($data);
        }

        /**
         * @param       $queryName
         * @param array $args
         *
         * @return int
         */
        public function remove($queryName, $args = array()) {
            return $this->removeWithName($queryName . serialize($args));
        }

        /**
         * @param $key
         *
         * @return int
         */
        public function removeWithName($key) {
            $result = 0;
            // se key ha l'asterisco, cancella tutte le chiavi che iniziano per il prefisso in $key
            if (strpos($key, '*') !== false) {
                foreach ($this->redis->keys($key) as $k) {
                    $result += $this->redis->del($k);
                }
            } else {
                $result += $this->redis->del($key);
            }
            return $result;
        }

        /**
         * @param $data
         *
         * @return string
         */
        private function serialize($data) {
            return serialize($data);
        }

        /**
         * @param $data
         *
         * @return mixed
         */
        private function unserialize($data) {
            return unserialize($data);
        }
    }
