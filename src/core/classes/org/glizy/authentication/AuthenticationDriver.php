<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

interface org_glizy_authentication_AuthenticationDriver
{
	public function login($loginId, $password, $remember=false);
	public function logout();
}
