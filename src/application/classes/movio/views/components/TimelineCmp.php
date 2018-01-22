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

        $timeline = array(  'events' => array(),
                            'type' => 'default',
                            'headline' => $content['title'],
                            'text' => $content['subtitle'],
                            'startDate' => movio_utils_TimelineDates::formatDate($content['startDate'])
                            );

        $num = count($content['timelineDef']);
        for ($i=0; $i < $num; $i++) {
            $temp = array('text' => array('headline' => '', 'text' => ''), 'media' => array('url' => '', 'caption' => '', 'thumbnail' => ''));

            if ($content['timelineDef'][$i]->startDate) $temp['start_date'] = movio_utils_TimelineDates::formatDate($content['timelineDef'][$i]->startDate);

            if ($content['timelineDef'][$i]->endDate) $temp['end_date'] = movio_utils_TimelineDates::formatDate($content['timelineDef'][$i]->endDate);
            
            if ($content['timelineDef'][$i]->headline or $content['timelineDef'][$i]->text) {
            	$temp['text'] = array(
            		'headline' => $content['timelineDef'][$i]->headline,
            		'text' => $content['timelineDef'][$i]->text
            	);
            }
            
            if ($content['timelineDef'][$i]->mediaExternal) {
                $temp['media'] = array(
                	'url' => $content['timelineDef'][$i]->mediaExternal,
                	'caption' => $content['timelineDef'][$i]->mediaCaption
                );
            } else if ($content['timelineDef'][$i]->media['mediaId'] > 0) {
            	$temp['media'] = array(
            		'url' => GLZ_HOST.'/'.org_glizy_helpers_Media::getResizedImageUrlById($content['timelineDef'][$i]->media['mediaId'], false, __Config::get('IMAGE_WIDTH'), __Config::get('IMAGE_HEIGHT') ),
            		'thumbnail' => GLZ_HOST.'/'.org_glizy_helpers_Media::getResizedImageUrlById($content['timelineDef'][$i]->media['mediaId'], false, __Config::get('THUMB_WIDTH'), __Config::get('THUMB_HEIGHT') ),
                	'caption' => $content['timelineDef'][$i]->mediaCaption
            	);
            }
            
            $timeline['events'][] = $temp;
        }
        return $timeline;
    }
}