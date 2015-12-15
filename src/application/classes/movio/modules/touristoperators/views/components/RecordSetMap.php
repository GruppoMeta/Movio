<?php
class movio_modules_touristoperators_views_components_RecordSetMap extends org_glizy_components_RecordSetList
{
    function process()
    {
        parent::process();

        $it = $this->_content->records;
        $siteProp = $this->_application->getSiteProperty();
        $map = array();
        $map['title'] = $this->_content->title;
        $map['total'] = $this->_content->total;
        $map['style'] = 'width: 100%; height: 600px';
        $map['geo'] = '';
        $map['text'] = '';
        $map['pathEnable'] = '0';
        $map['markers'] = array();
        $map['jsSrc'] = 'https://maps.googleapis.com/maps/api/js'.
                            (isset($siteProp['googleMapsApiKey']) ? '?key='.$siteProp['googleMapsApiKey'] : '');

        $imageWidth = __Config::get('IMG_LIST_WIDTH');
        $imageHeight = __Config::get('IMG_LIST_HEIGHT');
        foreach ($it as $ar) {
            $image = '';
            $images = org_glizy_helpers_Convert::formEditObjectToStdObject($ar->images);
            if (count($images)) {
                $media = json_decode($images[0]->image);
                if ($media && @$media->id) {
                    $image = '<img src="'.GLZ_HOST.'/getImage.php?w='.$imageWidth.'&h='.$imageHeight.'&id='.$media->id.'">';
                }
            }

            $localtions = org_glizy_helpers_Convert::formEditObjectToStdObject($ar->locations);
            foreach($localtions as $l) {
                $geo = explode(',', $l->coordinates);
                $map['markers'][] = array(
                    'lat' => $geo[0],
                    'long' => $geo[1],
                    'zoom' => $geo[2],
                    'title' => $l->locationDescription!=$ar->title ? $ar->title.' - '.$l->locationDescription : $l->locationDescription,
                    'text' => $l->location,
                    'image' => $image,
                    'link' => __Link::makeSimpleLink(__T('Read'), $ar->__url__)
                );
            }
        }

        if (count($map['markers'])) {
            $map['markers'] = json_encode($map['markers']);
            $map['geo'] = json_encode($map['markers'][0]);
            $map['text'] = $map['markers'][0]->title;
        }

        $this->_content = $map;
    }
}