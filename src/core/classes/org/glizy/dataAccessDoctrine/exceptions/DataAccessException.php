<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizy_dataAccessDoctrine_exceptions_DataAccessException extends Exception
{
    public static function unknownColumn($name, $tableName)
    {
        return new self('Unknown column '.$name.' in table '.$tableName);
    }
}