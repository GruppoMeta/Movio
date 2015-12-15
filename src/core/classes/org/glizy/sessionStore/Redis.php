<?php
class org_glizy_sessionStore_Redis implements SessionHandlerInterface
{
    protected $lifetime;
    protected $redis;
    protected $prefix;

    public function __construct($lifetime, $prefix) {
        $this->redis = org_glizy_redis_Client::getConnection();
        $this->lifetime = (int)$lifetime;
        $this->prefix = $prefix.':';
    }

    public function open($savePath, $sessionName) {
    }

    public function close() {
        $this->redis = null;
        unset($this->redis);
    }

    public function read($id) {
        $sessData = $this->redis->get($this->prefix.$id);
        $this->redis->expire($this->prefix.$id, $this->lifetime);
        return $sessData;
    }

    public function write($id, $data) {
        $this->redis->set($this->prefix.$id, $data);
        $this->redis->expire($this->prefix.$id, $this->lifetime);
    }

    public function destroy($id) {
        $this->redis->del($this->prefix.$id);
    }

    public function gc($maxLifetime) {
    }
}