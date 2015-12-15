<?php

interface org_glizy_components_interfaces_IDataProvider
{
    public function &loadQuery($queryName='', $options=array());
    public function &load($id);
}