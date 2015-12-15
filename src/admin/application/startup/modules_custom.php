<?php

if ( file_exists( '../application/startup/modules_custom.php' ) )
{
    __Paths::addClassSearchPath( __Paths::get( 'APPLICATION_TO_ADMIN' ).'classes/userModules/' );
    include('../application/startup/modules_custom.php');
}