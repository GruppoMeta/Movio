<?php
class movio_modules_touristoperators_views_components_RecordDetail extends org_glizy_components_RecordDetail
{
    function getContent()
    {
        parent::getContent();

        // create the GooglMap data
        $map = array();
        $map['style'] = 'width: 100%; height: 400px';
        $map['geo'] = '';
        $map['text'] = '';
        $map['pathEnable'] = '0';
        $map['markers'] = array();

        $googleApiKey = __Config::get('glizy.maps.google.apiKey');
        $map['jsSrc'] = 'http://maps.google.com/maps/api/js?key='.$googleApiKey;

        if (empty($googleApiKey)) {
            $this->addOutputCode('<link rel="stylesheet" href="https://unpkg.com/leaflet@1.3.4/dist/leaflet.css" integrity="sha512-puBpdR0798OZvTTbP4A8Ix/l+A4dHDD0DGqYW6RQ+9jxkRFclaxxQb/SJAWZfWAkuyeQUytO7+7N4QKrDh+drA==" crossorigin=""/>', 'head');
            $this->addOutputCode('<script src="https://unpkg.com/leaflet@1.3.4/dist/leaflet.js" integrity="sha512-nMMmRyTVoLYqjP9hrbed9S+FzjZHW5gY1TWCHA5ckwXZBadntCNs8kEqAWdrb9O7rxbCaA4lKTIWjDXZxflOcA==" crossorigin=""></script>', 'head');
        }

        foreach($this->_content->locations as $l) {
            $geo = explode(',', $l->coordinates);
            $map['markers'][] = array(
                'lat' => $geo[0],
                'long' => $geo[1],
                'zoom' => $geo[2],
                'title' => $l->locationDescription,
                'text' => $l->location,
                'image' => '',
                'link' => ''
            );
        }

        if (count($map['markers'])) {
            $map['geo'] = json_encode($map['markers'][0]);
            $map['text'] = $map['markers'][0]['title'];
            $map['markers'] = json_encode($map['markers']);
            $this->_content->map = $map;
        }
        return $this->_content;
    }
}