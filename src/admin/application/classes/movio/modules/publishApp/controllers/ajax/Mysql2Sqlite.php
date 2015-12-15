<?php
class movio_modules_publishApp_controllers_ajax_Mysql2Sqlite extends org_glizy_mvc_core_CommandAjax
{
    function execute($sqliteDb)
    {
        if ($this->user->isLogged())
        {
            $dbHost = __Config::get('DB_HOST');
            $dbUser = __Config::get('DB_USER');
            $dbPass = __Config::get('DB_PSW');
            $dbName = __Config::get('DB_NAME');
            
            $tables= array(
                array(
                    'name' => __Config::get('movio.modules.publishApp.mobileCodesTable'),
                    'type' => 'normal'
                ),
                array(
                    'name' => __Config::get('movio.modules.publishApp.mobileContentsTable'),
                    'type' => 'normal'
                ),
                array(
                    'name' => __Config::get('movio.modules.publishApp.mobileFulltextTable'),
                    'type' => 'fulltext'
                )
            );
            
            $mysql2SqliteService = org_glizy_ObjectFactory::createObject('movio.modules.publishApp.service.Mysql2SqliteService');
            $mysql2SqliteService->convert($dbHost, $dbUser, $dbPass, $dbName, $tables, $sqliteDb);
        }
    }
}