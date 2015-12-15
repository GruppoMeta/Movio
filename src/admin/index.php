<?php
require_once("../core/core.inc.php");

$application = org_glizy_ObjectFactory::createObject('org.glizycms.core.application.AdminApplication', 'application', '../', '../application/');
__Paths::set('APPLICATION_TO_ADMIN_TEMPLATE', '../static/movio/templates/');
$application->useXmlSiteMap = true;
$application->setLanguage('it');
$application->run();