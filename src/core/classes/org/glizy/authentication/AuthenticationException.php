<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizy_authentication_AuthenticationException extends Exception
{
    const EMPTY_LOGINID_OR_PASSWORD = 1;
    const WRONG_LOGINID_OR_PASSWORD = 2;
    const USER_NOT_ACTIVE = 3;
    const ACCESS_NOT_ALLOWED = 4;

    public static function emptyLoginIdOrPassword()
    {
        return new self('Empty username or password', self::EMPTY_LOGINID_OR_PASSWORD);
    }

    public static function wrongLoginIdOrPassword()
    {
        return new self('Wrong username or password', self::WRONG_LOGINID_OR_PASSWORD);
    }

    public static function userNotActive()
    {
        return new self('User not active', self::USER_NOT_ACTIVE);
    }

    public static function AccessNotAllowed()
    {
        return new self('Access Not Allowed', self::ACCESS_NOT_ALLOWED);
    }
}