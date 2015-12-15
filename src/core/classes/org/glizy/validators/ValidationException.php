<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizy_validators_ValidationException extends Exception
{
    private $errors;
    
    public function __construct($errors)
    {
        $this->errors = $errors;
        parent::__construct("There were validation errors, call getErrors() to get them");
    }
    
    public function getErrors()
    {
        return $this->errors;
    }
}
