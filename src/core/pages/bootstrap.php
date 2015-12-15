<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

require_once("core/core.inc.php");

$application = &org_glizy_ObjectFactory::createObject('org.glizy.application.ApplicationDB', 'INSERT HERE THE APPLICATION FOLDER');
$application->run();

