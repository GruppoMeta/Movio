<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_compilers_PageTypeException extends Exception
{
    public static function templateDefineNotValid($src)
    {
        return new self('The template define tag must have name attribute, file: '.$src);
    }

	public static function templateDefinitionDontExixts($name)
    {
        return new self('The template definition don\'t exixts: '.$name);
    }

	public static function templateDefinitionRequired($name, $src)
    {
        return new self('The template definition "'.$name.'" is required, file: '.$src);
    }
}
