<?php

class org_glizy_types_Time extends DateTime
{
    public static function createFromFormat($format, $time)
    {
        return new self($time); 
    }
    
    public function __toString()
    {
        return $this->format('H:i:s');
    }
}
