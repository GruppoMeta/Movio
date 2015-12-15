<?php
ini_set('max_execution_time', 600);

class movio_modules_publishApp_controllers_ajax_Export extends org_glizy_mvc_core_CommandAjax
{
    function execute($mediaPath, $languageId, $languageCode, $menuIdArray, $title, $subtitle, $creditPageId, $isExhibitionActive)
    {
        if ($this->user->isLogged())
        {
            $mobileContentsTable = __Config::get('movio.modules.publishApp.mobileContentsTable');
            org_glizy_dataAccessDoctrine_DataAccess::truncateTable($mobileContentsTable);

            $mobileFulltextTable = __Config::get('movio.modules.publishApp.mobileFulltextTable');
            org_glizy_dataAccessDoctrine_DataAccess::truncateTable($mobileFulltextTable);
            
            $exportService = org_glizy_ObjectFactory::createObject('movio.modules.publishApp.service.ExportService');
            $exportService->export($languageId, $languageCode, $menuIdArray, $title, $subtitle, $creditPageId, $isExhibitionActive);
            $medias = $exportService->getMedias();
        
            foreach ($medias as $id => $fileName) {
                $media = org_glizycms_mediaArchive_MediaManager::getMediaById($id);
                @copy($media->getFileName(), $mediaPath.$fileName);
            }
            
            return $exportService->getGraphs();
        }
    }
}