<?php

class movio_modules_thesaurus_views_components_DictionaryListCmp extends org_glizy_components_ComponentContainer
{

    private $type;

    function process() {
        $dictionaryId = $this->loadContent($this->getId().'-dictionaryId');
        parent::process();
        if (!$this->_application->isAdmin()) {
            $thesaurusProxy = org_glizy_ObjectFactory::createObject('movio.modules.thesaurus.models.proxy.ThesaurusProxy');
            $this->type = $thesaurusProxy->getTypeByDictionaryId($dictionaryId);
            $this->_content = array('type' => $this->type, 'records' => array());
            
			$siteProps = $this->_application->getSiteProperty();            
            $this->_content['googleMapsURL'] = 'http://maps.google.com/maps/api/js?key=' . $siteProps['googleMapsApiKey'];
            
            $this->readTermsList($dictionaryId);
        }
    }

    function process_ajax()
    {
        $c = $this->getRootComponent();
        $c->process();
        $content = $this->stripIdFromContent(parent::getContent());
        $timeline = array(  'events' => array(),
                            'type' => 'default',
                            'startDate' => movio_utils_TimelineDates::formatDate($content['startDate'])
                            );
        $num = count($content['records']);
        if ($this->type == 'chronologic') {
        	foreach ($content['records'] as $record) {
        		$term = $record['term'];
                $temp = array('text' => array());
                $temp['text']['headline'] = $term->term;
                
                $docs = array();
                foreach ($record['taggedDocuments'] as $doc) {
                    $docs[] = __Link::makeSimpleLink($doc['title'], $doc['url']);
                }
                $temp['text']['text'] = implode(' - ', $docs);
                
                if ($term->dateFrom)
                	$temp['start_date'] = movio_utils_TimelineDates::formatDate($term->dateFrom);
                	
                if ($term->dateTo)
                	$temp['end_date'] = movio_utils_TimelineDates::formatDate($term->dateTo);
                
	            $timeline['events'][] = $temp;
        	}
        }

        return $timeline;
    }


    function render($outputMode = NULL, $skipChilds = false) {
        if (!$this->_application->isAdmin()) {
            $this->setAttribute('skin', 'DictionaryList.html');
        }
        parent::render($outputMode, $skipChilds);
    }


    private function readTermsList($dictionaryId)
    {
        if ($dictionaryId) {
            $thesaurusProxy = org_glizy_ObjectFactory::createObject('movio.modules.thesaurus.models.proxy.ThesaurusProxy');
            $this->_content['records'] = $thesaurusProxy->getDocumentsWithDictionaryOrTerm($dictionaryId);
        }

        if ($this->type == 'geographical') {
            $this->_content['records'] = json_encode($this->_content['records']);
        }  else if ($this->type == 'chronologic') {

            $this->_content['source'] = GLZ_HOST.'/'.$this->getAjaxUrl().'&.json';
            $this->_content['font'] = 'PT';
            $this->_content['lang'] = $this->_application->getLanguage();
        }
    }
}