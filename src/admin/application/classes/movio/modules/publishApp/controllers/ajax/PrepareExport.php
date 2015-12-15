<?php
class movio_modules_publishApp_controllers_ajax_PrepareExport extends org_glizy_mvc_core_CommandAjax
{
    function execute($exportPath, $languages, $mediaPath, $graphPath, $zipFolder, $zipFile)
    {
        if ($this->user->isLogged())
        {
            org_glizy_helpers_Files::deleteDirectory($exportPath);
            @unlink($zipFile);
            
            @mkdir($exportPath);
            @mkdir($mediaPath);
            @mkdir($graphPath);
            @mkdir($graphPath.'document');
            
            foreach ($languages as $language) {
                $ar = __ObjectFactory::createModel('org.glizycms.core.models.Language');
                $ar->load($language);
                @mkdir($graphPath.$ar->language_code);
                @mkdir($graphPath.'document/'.$ar->language_code);
            }

            @mkdir($zipFolder);
            @chmod($zipFolder, 0777);
            
            $mobileCodesTable = __Config::get('movio.modules.publishApp.mobileCodesTable');
            org_glizy_dataAccessDoctrine_DataAccess::truncateTable($mobileCodesTable);
        }
    }
}