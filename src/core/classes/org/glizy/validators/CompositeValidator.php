<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizy_validators_CompositeValidator implements org_glizy_validators_ValidatorInterface
{
    /**
     * @var org_glizy_validators_ValidatorInterface[]
     */
    private $validators = array();

    /**
     * @param string $description
     * @param string $value
     *
     * @return bool|string
     */
    public function validate($description, $value, $defaultValue) {
        $errors = array();

        foreach ($this->validators as $validator) {
            $result = $validator->validate($description, $value, $defaultValue);
            if (is_string($result)) {
                $errors[] = $result;
            }
        }

        return empty($errors) ? true : $errors;
    }

    /**
     * @param org_glizy_validators_ValidatorInterface $validator
     */
    public function add($validator) {
        $this->validators[] = $validator;
    }

    /**
     * @param org_glizy_validators_ValidatorInterface[] $validators
     *
     * @return void
     */
    public function addArray($validators) {
        $this->validators = array_merge($this->validators, $validators);
    }
}
