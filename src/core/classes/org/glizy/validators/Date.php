<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 * 
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizy_validators_Date implements org_glizy_validators_ValidatorInterface
{
    /**
     * @param string $description
     * @param string $value
     *
     * @return bool|string
     */
    public function validate($description, $value, $defaultValue)
    {
        if (preg_match('/^[\d]{2,4}-[\d]{1,2}-[\d]{1,2}$/', $value) || empty($value)) {
            return true;
        }

            return $description . " deve essere una data";
    }
}