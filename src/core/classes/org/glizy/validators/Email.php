<?php

/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */
class org_glizy_validators_Email implements org_glizy_validators_ValidatorInterface
{

    /**
     * @param string $description
     * @param string $value
     *
     * @return bool|string
     */
    public function validate($description, $value, $defaultValue)
    {
        if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return true;
        } else {
            return $description . " deve essere un'email";
        }
    }
}