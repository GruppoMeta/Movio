<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizy_compilers_ModelException extends Exception
{
    public static function missingTableName($fileName)
    {
        return new self('Model '.$fileName.' without tablename attribute.');
    }

    public static function queryWithoutName($fileName)
    {
        return new self('Model '.$fileName.' with query define without name.');
    }

    public static function scriptParentError($fileName)
    {
        return new self('Model '.$fileName.' Script tag with wrong parent.');
    }
}