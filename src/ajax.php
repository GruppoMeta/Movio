<?php
ob_start();
require_once("core/core.inc.php");

$application = org_glizy_ObjectFactory::createObject('org.glizycms.core.application.Application', 'application');
org_glizy_Paths::addClassSearchPath('admin/application/classes/');
$application->runAjax();
