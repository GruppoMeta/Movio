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
    /**
     * @var array
     */
    private $errors;
    
    public function __construct($errors)
    {
        $this->errors = $errors;
        parent::__construct(sprintf("There were validation errors: %s", implode("\n", $this->errors)));
    }
    
    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
