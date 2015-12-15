<?php
ini_set('max_execution_time', 600);

class movio_modules_publishApp_controllers_ajax_CreateZip extends org_glizy_mvc_core_CommandAjax
{
    function execute($exportPath, $zipFile)
    {
        if ($this->user->isLogged())
        {
            require_once(__Paths::get('APPLICATION').'/libs/dZip.inc.php');
            $zip = new dZip($zipFile);
            $this->addFolderToZip( $zip, $exportPath, $exportPath);
            $zip->save();
            
            org_glizy_Registry::set('movio/modules/publishApp/lastUpdate', strval(time()));
        }
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