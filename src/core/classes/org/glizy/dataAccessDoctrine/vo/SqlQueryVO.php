<?php

class org_glizy_dataAccessDoctrine_vo_SqlQueryVO
{
    private $sql = '';

    function __construct($sql)
    {
        $this->sql = $sql;
    }

    public function toArray()
    {
        return array('sql' => $this->sql);
    }

}