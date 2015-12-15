<?php
class movio_modules_publishApp_controllers_ajax_GetSteps extends org_glizy_mvc_core_CommandAjax
{
    function execute($languages, $menuIdArray, $title, $subtitle, $creditPageId, $isExhibitionActive)
    {
        if ($this->user->isLogged())
        {
            $languages = is_array($languages) ? $languages : array($languages);
            $exportPath = __Paths::get('CACHE').'export/';
            $mediaPath = $exportPath.'media/';
            $graphPath = $exportPath.'graph/';
            $zipFolder =  __Paths::get('BASE').'export/';
            $zipFile = $zipFolder.'mobileContents.zip';

            $creditPageId = str_replace('internal:', '', $creditPageId);

            $steps = array();

            $steps[] = array('action' => 'PrepareExport', 'params' => array('exportPath' => $exportPath, 'languages' => $languages, 'mediaPath' => $mediaPath, 'graphPath' => $graphPath, 'zipFolder' => $zipFolder, 'zipFile' => $zipFile));
            $steps[] = array('action' => 'ExportCodes');

            foreach ($languages as $languageId) {
                $ar = __ObjectFactory::createModel('org.glizycms.core.models.Language');
                $ar->load($languageId);
                $sqliteDb = $exportPath.$ar->language_name.'.db';
                $languageCode = $ar->language_code;

                $steps[] = array('action' => 'Export', 'params' => array('mediaPath' => $mediaPath, 'languageId' => $languageId, 'languageCode' => $languageCode, 'menuIdArray' => $menuIdArray, 'title' => $title, 'subtitle' => $subtitle, 'creditPageId' => $creditPageId, 'isExhibitionActive' => $isExhibitionActive));
                $steps[] = array('action' => 'Mysql2Sqlite', 'params' => array('exportPath' => $exportPath, 'sqliteDb' => $sqliteDb));
            }

            $steps[] = array('action' => 'CreateJSON', 'params' => array('exportPath' => $exportPath, 'languages' => $languages));
            $steps[] = array('action' => 'CreateZip', 'params' => array('exportPath' => $exportPath, 'mediaPath' => $mediaPath, 'zipFile' => $zipFile));

            return $steps;
        }
    }
}