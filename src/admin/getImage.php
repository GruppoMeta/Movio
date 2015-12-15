<?php
if (!defined('GLZ_LOADED'))
{
    require_once('../core/core.inc.php');
    $application = org_glizy_ObjectFactory::createObject('org.glizycms.core.application.Application', '../application', '../');
    org_glizy_Paths::addClassSearchPath('application/classes/');

    if (file_exists(org_glizy_Paths::get('APPLICATION_STARTUP')))
    {
        // if the startup folder is defined all files are included
        glz_require_once_dir(org_glizy_Paths::get('APPLICATION_STARTUP'));
    }
}


include('../getImage.php');