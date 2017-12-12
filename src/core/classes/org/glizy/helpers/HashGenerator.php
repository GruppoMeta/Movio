<?php
use Hashids\Hashids;

class org_glizy_helpers_HashGenerator extends GlizyObject
{
    protected $hashids;

    function __construct()
    {
        $this->hashids = new Hashids(__Config::get('glizy.helpers.Hash.salt'), 0, '0123456789abcdefghijklmnopqrstuvwxyz');
    }

    public function encode($s)
    {
        return $this->hashids->encode($s);
    }

    public function decode($s)
    {
        $v = $this->hashids->decode($s);
        return $v[0];
    }
}