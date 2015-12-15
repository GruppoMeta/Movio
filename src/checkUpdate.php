<?php
require_once("core/core.inc.php");

$application = org_glizy_ObjectFactory::createObject('org.glizycms.core.application.Application', 'application');
org_glizy_Paths::addClassSearchPath('admin/application/classes/');
$application->runSoft();

$timestamp = __Request::get('timestamp');
$lastUpdate = org_glizy_Registry::get('movio/modules/publishApp/lastUpdate');
$result = array(
    'updated' => $lastUpdate > $timestamp,
    'lastUpdate' => $lastUpdate,
    'fileSize' => @filesize(__Paths::getRealPath('BASE').'export/mobileContents.zip'),
    'url' => GLZ_HOST.'/export/'.'mobileContents.zip',
    'checkUpdateUrl' => GLZ_HOST.'/checkUpdate.php'
);

header("Content-Type: application/json");
echo json_encode($result);