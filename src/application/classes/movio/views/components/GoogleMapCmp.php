<?php
class movio_views_components_GoogleMapCmp extends org_glizy_components_Groupbox
{
    function getContent()
    {
        $content = $this->stripIdFromContent(parent::getContent());
        $markers = array();
        $geo = explode(',', $content['geo']);
        $content['geo'] = json_encode(array('lat' => $geo[0], 'long' => $geo[1], 'zoom' => intval($geo[2])));
        $content['pathEnable'] = $content['pathEnable'];
        $markers[] = array('lat' => $geo[0], 'long' => $geo[1], 'title' => $content['text'], 'text' => '', 'image' => '', 'link' => '');
        foreach($content['markers'] as $poi) {
            $link = '';
            if ($poi->type == 'internalLink' && $poi->internalLink) {
                /*TODO quando riesco a recuperare il titolo della pagina metto a false per internal link*/
                $link = $this->limitURL($poi->internalLink, 25, true);
            } elseif ($poi->type == 'externalLink' && $poi->externalLink) {
                $link = $this->limitURL($poi->externalLink, 25, true);
            }

            $geo = explode(',', $poi->poi);
            $markers[] = array(
                'lat' => $geo[0],
                'long' => $geo[1],
                'title' => $poi->title,
                'image' => $poi->image['__html__'],
                'text' => $poi->text,
                'link' => $link
            );
        }
        $geo = $geo = explode(',', $content['geo']);
        $content['markers'] = json_encode($markers);
        $content['style'] = 'width: '.($content['width'] ? $content['width'].'px' : '100%').'; height: '.($content['height'] ? $content['height'].'px' : '600px');

        $siteProp = $this->_application->getSiteProperty();
        $apiKey = @$siteProp['googleMapsApiKey'] ?: __Config::get('glizy.maps.google.apiKey');

        if (!$apiKey) {
            //TODO: far presente all'utente che manca l'APIKey, in qualche modo
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

    private function limitURL($url, $limit, $external)
    {
        $link = '';
        if($external)
        {
            if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
                $title = $url;
                $url = "http://" . $url;
            } else {
                $title = substr($url, stripos('.', -1));
            }
            if (strlen($url) > $limit) {
                $shorter = substr($title, 0, $limit) . '...';
                return '<a href="' . $url . '" title="' . $url . '" rel="external">' . $shorter . '</a>';
            }

            return '<a href="' . $url . '" title="' . $url . '" rel="external">' . $title . '</a>';
        }
        /*TODO per intenal link mettere titolo pagina*/
        return '<a href="' . $url . '" title="' . $url . '">' . $url . '</a>';
    }
}
