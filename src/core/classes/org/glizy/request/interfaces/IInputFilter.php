<?php

interface org_glizy_request_interfaces_IInputFilter
{
    public function filter($values, $excludedFields=null);
}