<?php
class movio_views_components_TimelineCmp extends org_glizy_components_Groupbox
{
    function getContent() {
        $content = parent::getContent();
        $content = $this->stripIdFromContent($content);

        $result = array();
        $result['font'] = 'PT';
        $result['maptype'] = 'ROADMAP';
        $result['lang'] = $this->_application->getLanguage();
        // $result['title'] = $content['title'];
        // $result['subtitle'] = $content['subtitle'];
        // $result['startDate'] = $content['startDate'];
        $result['width'] = $content['width'] ? $content['width'] : '100%';
        $result['height'] = $content['height'] ? $content['height'] : '600';
        if ($content['url']) {
            $result['source'] = $content['url'];
        } else {
           $result['source'] = GLZ_HOST.'/'.$this->getAjaxUrl().'&.json';
        }

        return $result;
    }

    function render($mode)
    {
        if (!$this->_application->isAdmin()) {
             $this->setAttribute('skin', 'Timeline.html');
        }
        parent::render($mode);
    }

    function process_ajax()
    {
        $c = $this->getRootComponent();
        $c->process();
        $content = $this->stripIdFromContent(parent::getContent());

        $timeline = array(  'date' => array(),
                            'type' => 'default',
                            'headline' => $content['title'],
                            'text' => $content['subtitle'],
                            'startDate' => $this->formatDate($content['startDate'])
                            );

        $num = count($content['timelineDef']);
        for ($i=0; $i < $num; $i++) {
            $temp = array('headline' => '', 'text' => '', 'asset' => array('media' => '', 'caption' => '', 'credit' => ''));
            if ($content['timelineDef'][$i]->startDate) $temp['startDate'] = $this->formatDate($content['timelineDef'][$i]->startDate);
            if ($content['timelineDef'][$i]->endDate) $temp['endDate'] = $this->formatDate($content['timelineDef'][$i]->endDate);
            if ($content['timelineDef'][$i]->headline) $temp['headline'] = $content['timelineDef'][$i]->headline;
            if ($content['timelineDef'][$i]->text) $temp['text'] = $content['timelineDef'][$i]->text;
            if ($content['timelineDef'][$i]->mediaExternal) {
                $temp['asset']['media'] = $content['timelineDef'][$i]->mediaExternal;
            } else if ($content['timelineDef'][$i]->media['mediaId'] > 0) {
                $temp['asset']['media'] = GLZ_HOST.'/'.org_glizy_helpers_Media::getResizedImageUrlById($content['timelineDef'][$i]->media['mediaId'], false, __Config::get('IMAGE_WIDTH'), __Config::get('IMAGE_HEIGHT') );
                $temp['asset']['thumbnail'] = GLZ_HOST.'/'.org_glizy_helpers_Media::getResizedImageUrlById($content['timelineDef'][$i]->media['mediaId'], false, __Config::get('THUMB_WIDTH'), __Config::get('THUMB_HEIGHT') );
            }
            if ($content['timelineDef'][$i]->mediaCaption) $temp['asset']['caption'] = $content['timelineDef'][$i]->mediaCaption;
            $timeline['date'][] = $temp;
        }
        return array('timeline' => $timeline);
    }

    private function formatDate($date)
    {
        $date = preg_replace('/^(\d{1,2})\/(\d{1,2})\/(-?\d{2,4})$/', '$3/$2/$1', $date);
        $date = preg_replace('/^(\d{1,2})\/(-?\d{2,4})$/', '$2', $date);
        $date = preg_replace('/^(-?\d{2,4})$/', '$1', $date);

        return str_replace('/', ',', $date);
    }
}