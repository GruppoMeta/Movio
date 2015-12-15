<?php
class movio_modules_publishApp_controllers_ajax_SaveSVG extends org_glizy_mvc_core_CommandAjax
{
    function execute($languageCode, $id, $svg, $type)
    {
        if ($this->user->isLogged())
        {
            $exportPath = __Paths::get('CACHE').'export/';
            $graphPath = $exportPath.'graph/'.$type.'/'.$languageCode.'/'.$id.'.svg';
            file_put_contents($graphPath, $svg);
        }
    }
}