<?php
/**
 * Application  class.
 *
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizycms_core_application_ApplicationException extends Exception
{
    public static function notDefaultLanguage()
    {
        return new self('Default language not defined');
    }
}
