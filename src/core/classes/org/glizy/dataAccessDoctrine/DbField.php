<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */



class org_glizy_dataAccessDoctrine_DbField
{
    const NOT_INDEXED = 0;  // non indicizzato
    const INDEXED = 1;      // indicizzato
    const FULLTEXT = 2;     // indice fulltext
    const ONLY_INDEX = 16;    // indice fulltext

    public $name;
    public $type;
    public $size;
    public $key;
    public $validator; // viene settato dal compilatore del model
    public $defaultValue;
    public $readFormat;
    public $virtual;
    public $description; // con la label del campo si usa questo
    public $index; // tre costanti non indicizzato, indicizzato, fulltext
    public $option;
    public $isSystemField = false;

    function __construct($name, $type, $size, $key, $validator, $defaultValue, $readFormat=true, $virtual=false, $description='', $index=self::INDEXED, $option=null)
    {
        $this->name = $name;
        $this->type = $type;
        $this->size = $size;
        $this->key = $key;
        $this->validator = $validator;
        $this->defaultValue = $defaultValue;
        $this->readFormat = $readFormat;
        $this->virtual = $virtual;
        $this->description = $description !== '' ? $description : $name;
        $this->index = $index;
        $this->option = $option;


        if (    $this->type == org_glizy_dataAccessDoctrine_types_Types::ARRAY_ID &&
                (is_null($this->option) || !isset($this->option[org_glizy_dataAccessDoctrine_types_Types::ARRAY_ID])) ) {
            // backward compatibility if the ARRAY_ID field don't have options
            // set the default values
            $this->option = array( org_glizy_dataAccessDoctrine_types_Types::ARRAY_ID => array(
                                        'type' => \Doctrine\DBAL\Types\Type::INTEGER,
                                        'field' => 'id'
                                    ));
        }
    }

    public function format($value, $connection)
    {
        return $this->readFormat ? $connection->convertToPHPValue($value, $this->type) : $value;
    }

    /**
     * @param org_glizy_validators_AbstractValidator $validator
     */
    public function addValidator($validator)
    {
        if (!$this->validator || (!$this->validator instanceof org_glizy_validators_CompositeValidator)) {
            $composite = new org_glizy_validators_CompositeValidator();
            if ($this->validator ) {
                $composite->add($this->validator);
            }
            $this->validator = $composite;
        }
        $this->validator->add($validator);
    }

}