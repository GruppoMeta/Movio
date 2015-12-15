<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 * 
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizy_validators_CompositeValidator extends org_glizy_validators_AbstractValidator
{
    private $validators = array();
    
    public function validate($description, $value) {
        $errors = array();
        
        foreach ($this->validators as $validator) {
            $result = $validator->validate($description, $value);
            if (is_string($result)) {
                $errors[] = $result; 
            }
        }
        
        return empty($errors) ? true : $errors;
    }
    
    public function add($validator) {
        $this->validators[] = $validator;
    }
    
    public function addArray($validators) {
        $this->validators = array_merge($this->validators, $validators);
    }
}
