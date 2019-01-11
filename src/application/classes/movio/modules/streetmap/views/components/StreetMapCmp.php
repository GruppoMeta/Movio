<?php
class movio_modules_streetmap_views_components_StreetMapCmp extends org_glizy_components_Groupbox
{
    function getContent()
    {
        $content = $this->stripIdFromContent(parent::getContent());
        $markers = array();
        $geo = explode(',', $content['geo']);
        $content['geo'] = json_encode(array('lat' => $geo[0], 'long' => $geo[1], 'zoom' => intval($geo[2])));
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
        $content['markers'] = json_encode($markers);
        $content['style'] = 'width: '.($content['width'] ? $content['width'].'px' : '100%').'; height: '.($content['height'] ? $content['height'].'px' : '600px');

        return $content;
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
