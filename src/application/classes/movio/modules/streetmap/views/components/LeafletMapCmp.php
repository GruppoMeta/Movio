<?php
class movio_modules_streetmap_views_components_LeafletMapCmp extends movio_modules_streetmap_views_components_StreetMapCmp
{
    function getContent()
    {
        $content = parent::getContent();

        $this->addOutputCode('<style type="text/css">.leaflet-popup-content img {width: auto; height: auto;}</style>');
        $this->addOutputCode('<link rel="stylesheet" href="https://unpkg.com/leaflet@1.3.4/dist/leaflet.css" integrity="sha512-puBpdR0798OZvTTbP4A8Ix/l+A4dHDD0DGqYW6RQ+9jxkRFclaxxQb/SJAWZfWAkuyeQUytO7+7N4QKrDh+drA==" crossorigin=""/>', 'head');
        $this->addOutputCode('<script src="https://unpkg.com/leaflet@1.3.4/dist/leaflet.js" integrity="sha512-nMMmRyTVoLYqjP9hrbed9S+FzjZHW5gY1TWCHA5ckwXZBadntCNs8kEqAWdrb9O7rxbCaA4lKTIWjDXZxflOcA==" crossorigin=""></script>', 'head');

        return $content;
    }

    function render($outputMode = NULL, $skipChilds = false)
    {
        if (!$this->_application->isAdmin()) {
            $this->setAttribute('skin', 'LeafletMap.html');
        }
        parent::render($outputMode, $skipChilds);
    }
}
