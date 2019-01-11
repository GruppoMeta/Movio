<?php
class movio_modules_streetmap_views_components_GoogleMapCmp extends movio_modules_streetmap_views_components_StreetMapCmp
{
    function getContent()
    {
        $content = parent::getContent();

        $siteProp = $this->_application->getSiteProperty();
        $apiKey = @$siteProp['googleMapsApiKey'] ?: __Config::get('glizy.maps.google.apiKey');
        if (!$apiKey) {
            $this->logAndMessage("È necessario che ci sia impostata una APIKey per la libreria GoogleMaps, o nel config del sistema (.xml) o nelle proprietà del sito in questione!", '', GLZ_LOG_WARNING);
        }
        $content['jsSrc'] = 'https://maps.googleapis.com/maps/api/js?key='.$apiKey;

        return $content;
    }

    function render($outputMode = NULL, $skipChilds = false)
    {
        if (!$this->_application->isAdmin()) {
            $this->setAttribute('skin', 'GoogleMap.html');
        }
        parent::render($outputMode, $skipChilds);
    }
}
