<?php
class movio_modules_touristoperators_views_components_RecordDetail extends org_glizy_components_RecordDetail
{
    function getContent()
    {
        parent::getContent();

        // create the GooglMap data
        $siteProp = $this->_application->getSiteProperty();
        $map = array();
        $map['style'] = 'width: 100%; height: 400px';
        $map['geo'] = '';
        $map['text'] = '';
        $map['pathEnable'] = '0';
        $map['markers'] = array();
        $map['jsSrc'] = 'https://maps.googleapis.com/maps/api/js'.
                            (isset($siteProp['googleMapsApiKey']) ? '?key='.$siteProp['googleMapsApiKey'] : '');

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
            $map['markers'] = json_encode($map['markers']);
            $map['geo'] = json_encode($map['markers'][0]);
            $map['text'] = $map['markers'][0]->title;
            $this->_content->map = $map;
        }
        return $this->_content;
    }
}