<?php

class movio_modules_modulesBuilder_builder_Builder extends GlizyObject
{
    function __construct()
    {
    }

    function execute()
    {
        $this->setCustomErrorHandler();
        $this->proceedWithSteps();
        $this->unsetCustomErrorHandler();
    }

    public function customErrorHandler($errno, $errstr, $errfile, $errline) {
        if (__Config::get("movio.debug.verbose")) {
            $this->logAndMessage("Errore durante il salvataggio del modulo: \r\n<br>\r\n" . implode("; ", func_get_args()), "", GLZ_LOG_WARNING);
        }
        return true;
    }

    private function setCustomErrorHandler()
    {
        set_error_handler(array($this, 'customErrorHandler'));
    }

    private function proceedWithSteps()
    {
        // crea le cartelle
        $sequence = array('01CreateFolders', '02SaveLocaleFiles', '03CreateModule', '04CreateModelFile', '05CreateAdminPage', '06CreatePage', '07CreateRoutingFile', '08AddPage', '09AddModuleDirective', '10AddSitemap', '11SaveModuleStructure', '12ImportCsvData');
        foreach ($sequence as $v) {
            $c = org_glizy_ObjectFactory::createObject('movio.modules.modulesBuilder.builder.' . $v, $this);

            try {
                $c->execute();
            } catch (Exception $ex) {
                $this->logAndMessage($ex->getMessage() . "\r\n<br>\r\n" . $ex->getTraceAsString(), "", GLZ_LOG_ERROR);
                __Request::set("MOVIO_BuilderErrorReport", $ex->getMessage() . "\r\n<br>\r\n" . $ex->getTraceAsString());
                break;
            }
        }
    }

    private function unsetCustomErrorHandler()
    {
        restore_error_handler();
    }

    function executeDelete()
    {
        // crea le cartelle
        $sequence = array('d01DeleteFolders', 'd02CleanStartup', 'd03DeleteMenu');
        foreach ($sequence as $v) {
            $c = org_glizy_ObjectFactory::createObject('movio.modules.modulesBuilder.builder.' . $v, $this);
            $r = $c->execute();
            if (!$r) {
                break;
            }
        }
    }

    function getModuleName()
    {
        return __Request::get('mbName');
    }

    function getTableNameDb()
    {
        return __Request::get('mbTableDB');
    }

    function getCustomModulesFolder($fullPath = true)
    {
        return __Paths::get('APPLICATION_TO_ADMIN') . 'classes/userModules/' . ($fullPath ? $this->getTableName() . '/' : '');
    }

    function getTableName()
    {
        return __Request::get('mbTable');
    }

    function getPageTypeFolder()
    {
        return __Paths::get('APPLICATION_TO_ADMIN') . 'pageTypes/';
    }
}