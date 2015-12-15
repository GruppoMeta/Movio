<?php
class movio_modules_thesaurus_Exception extends Exception
{
    public static function dictionaryCreationError($name)
    {
        return new self('Error creating dictionary: '.$name);
    }

    public static function termCreationError($term)
    {
        return new self('Error creating term: '.$term);
    }

    public static function termWrongType($type)
    {
        return new self('Term with wrong type: '.$type);
    }

    public static function DictWrongType($type)
    {
        return new self('Dictionary with wrong type: '.$type);
    }

}