<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizy_dataAccessDoctrine_SystemField extends org_glizy_dataAccessDoctrine_DbField
{
    function __construct($name, $type, $size, $key, $validator, $defaultValue, $readFormat=true, $virtual=false, $description='', $index=self::INDEXED)
    {
        parent::__construct($name, $type, $size, $key, $validator, $defaultValue, $readFormat, $virtual, $description, $index);
        $this->isSystemField = true;
    }
}