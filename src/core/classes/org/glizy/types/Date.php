<?php

class org_glizy_types_Date extends DateTime
{
    public static function createFromFormat($format, $time)
    {
        return new self($time); 
    }
    
    public function __toString()
    {
        return $this->format('Y-m-d');
    }
}
