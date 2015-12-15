<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 * 
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizy_validators_Date extends org_glizy_validators_AbstractValidator
{
    public function validate($description, $value)
    {
        if (preg_match('/^[\d]{2,4}-[\d]{1,2}-[\d]{1,2}$/', $value) || empty($value)) {
            return true;
        }
        else {
            return $description . " deve essere una data";
        }
    }
}