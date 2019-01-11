<?php
class movio_views_components_TimelineCmp extends org_glizy_components_Groupbox
{
    function process()
    {
        parent::process();

        $this->addOutputCode('<link title="timeline-styles" rel="stylesheet" href="https://cdn.knightlab.com/libs/timeline3/latest/css/timeline.css">', 'head');
        $this->addOutputCode('<script src="https://cdn.knightlab.com/libs/timeline3/latest/js/timeline.js"></script>', 'head');
    }

    function getContent() {
        $content = parent::getContent();
        $content = $this->stripIdFromContent($content);

        $timelineCount = count($content['timelineDef']);

        $result = array();
        $result['language'] = $this->_application->getLanguage();
        $result['width'] = $content['width'] ? (!is_numeric($content['width']) ? $content['width'] : $content['width'] . 'px') : '100%';
        $result['height'] = $content['height'] ? (!is_numeric($content['height']) ? $content['height'] : $content['height'] . 'px') : '600px';
        $result['dimensions'] = 'width: ' . $result['width'] . '; height: ' . $result['height'] . ';';
        if (is_numeric($content['start'])) {
            $result['start'] = intval($content['start']);
            $result['start'] = ($result['start'] > 0 and $result['start'] < $timelineCount) ? intval($content['start']) - 1 : 0;
        } else {
            $result['start'] = 0;
        }

        if ($content['url']) {
            $result['source'] = $content['url'];
        } elseif ($timelineCount) {
           $result['source'] = GLZ_HOST.'/'.$this->getAjaxUrl().'&.json';
        }

        return $result;
    }

    function render($outputMode = NULL, $skipChilds = false)
    {
        if (!$this->_application->isAdmin()) {
             $this->setAttribute('skin', 'Timeline.html');
        }
        parent::render($outputMode, $skipChilds);
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