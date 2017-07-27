<?php

class movio_modules_modulesBuilder_builder_01CreateFolders extends movio_modules_modulesBuilder_builder_AbstractCommand
{
    function execute()
    {
        $customModulesFolder = $this->parent->getCustomModulesFolder();

        $folders = array(
            $customModulesFolder. '',
            $customModulesFolder. '/models',
            $customModulesFolder. '/views',
            $customModulesFolder. '/images',
            $customModulesFolder. '/config',
            $customModulesFolder. '/locale'
        );

        // crea le cartelle
        // controlla se esiste la cartella che contiene tutti i moduli custom
        if (!file_exists($this->parent->getCustomModulesFolder(false))) {
            mkdir($this->parent->getCustomModulesFolder(false));
        }

        @array_map(
            function($dirPath){
                @mkdir($dirPath);
                @chmod($dirPath, 0777);
                if (!is_dir($dirPath) || !is_readable($dirPath) || !is_writable($dirPath)){
                    throw new Exception("Non è stato possibile creare la cartella \"$path\", creazione fallita");
                }
            },
            $folders
        );

        return true;
    }
}
