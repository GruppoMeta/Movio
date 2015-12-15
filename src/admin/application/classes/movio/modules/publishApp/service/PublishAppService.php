<?php
class movio_modules_publishApp_service_PublishAppService extends GlizyObject
{
    function onRegister()
    {
        
    }
    
    public function publish()
    {
        $exportPath = __Paths::get('CACHE').'export/';
        $mediaPath = $exportPath.'media/';
        $zipPath =  __Paths::get('BASE').'export/'.'mobileContents.zip';
        
        org_glizy_helpers_Files::deleteDirectory($exportPath);
        @unlink($zipPath);
        
        @mkdir($exportPath);
        @mkdir($mediaPath);
        
        $exportService = org_glizy_ObjectFactory::createObject('movio.modules.publishApp.service.ExportService');
        $exportService->export();
        $medias = $exportService->getMedias();
        
        foreach ($medias as $id => $fileName) {
            $media = org_glizycms_mediaArchive_MediaManager::getMediaById($id);
            copy($media->getFileName(), $mediaPath.$fileName);
        }
        
        $dbHost = __Config::get('DB_HOST');
        $dbUser = __Config::get('DB_USER');
        $dbPass = __Config::get('DB_PSW');
        $dbName = __Config::get('DB_NAME');
        $tableName = __Config::get('movio.modules.publishApp.mobileContentsTable');
        $sqliteDb = $exportPath.__Config::get('movio.modules.publishApp.sqliteDbName');
        
        $mysql2SqliteService = org_glizy_ObjectFactory::createObject('movio.modules.publishApp.service.Mysql2SqliteService');
        $mysql2SqliteService->convert($dbHost, $dbUser, $dbPass, $dbName, $tableName, $sqliteDb);
        
        $this->createZip($exportPath, $zipPath);
        org_glizy_Registry::set('movio/modules/publishApp/lastUpdate', time());
    }
    
    protected function createZip($exportPath, $zipPath)
    {
        require_once(__Paths::get('APPLICATION').'/libs/dZip.inc.php');
        $zip = new dZip( $zipPath );
        $this->addFolderToZip( $zip, $exportPath, $exportPath);
        $zip->save();
    }

    private function addFolderToZip( &$zip, $dir, $baseDir)
    {
        if ($dir_handle = @opendir($dir))
        {
            while ($file_name = readdir($dir_handle))
            {
                if ($file_name!="." &&  $file_name!=".." )
                {
                    if ( !is_dir("$dir/$file_name") )
                    {
                        $zipFileName = str_replace( $baseDir, '', "$dir/$file_name" );
                        $zip->addFile( $dir.'/'.$file_name, $zipFileName );
                    }
                    else
                    {
                        $this->addFolderToZip( $zip, "$dir/$file_name", $baseDir );
                    }
                }
            }
            closedir($dir_handle);
            return 0;
        }
        else
        {
            return "Could not open directory $dir";
        }
    }
}