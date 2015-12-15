<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 * 
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizy_validators_Numeric extends org_glizy_validators_AbstractValidator
{
    public function validate($description, $value)
    {
        if (is_numeric($value) || empty($value)) {
            return true;
        }
        else {
            return $description . " deve essere un numero intero";
        }
    }
}