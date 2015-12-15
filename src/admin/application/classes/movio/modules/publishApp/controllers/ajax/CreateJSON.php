<?php
class movio_modules_publishApp_controllers_ajax_CreateJSON extends org_glizy_mvc_core_CommandAjax
{
    function execute($exportPath, $languages)
    {
        if ($this->user->isLogged())
        {
            $json = array();
            
            foreach ($languages as $language) {
                $ar = __ObjectFactory::createModel('org.glizycms.core.models.Language');
                $ar->load($language);
                $languageObj = new StdClass();
                $languageObj->code = $ar->language_code;
                $languageObj->label = $ar->language_name;
                $languageObj->file = $ar->language_name.'.db';
                $json[] = $languageObj;
            }
            
            file_put_contents($exportPath.'package.json', json_encode($json));
        }
    }
}