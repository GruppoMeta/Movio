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
            $this->readTermsList($dictionaryId, $this->type);
        }
    }

    function process_ajax()
    {
        $c = $this->getRootComponent();
        $c->process();
        $content = $this->stripIdFromContent(parent::getContent());
        $timeline = array(  'date' => array(),
                            'type' => 'default',
                            );
        $num = count($content['records']);
        if ($this->type == 'chronologic') {
            for ($i=0; $i < $num; $i++) {
                $term = $content['records'][$i]['term'];
                $temp = array('headline' => '', 'text' => '', 'asset' => array('media' => '', 'caption' => '', 'credit' => ''));
                $temp['headline'] = $term->term;
                if ($term->dateFrom) $temp['startDate'] = $this->formatDate($term->dateFrom);
                if ($term->dateTo) $temp['endDate'] = $this->formatDate($term->dateTo);
                $temp['text'] = '';
                foreach($content['records'][$i]['taggedDocuments'] as $doc) {
                    $temp['text'] = __Link::makeSimpleLink($doc['title'], $doc['url']).'<br/>';
                }
            $timeline['date'][] = $temp;
            }
        }

        return array('timeline' => $timeline);
    }


    function render($mode) {
        if (!$this->_application->isAdmin()) {
            $this->setAttribute('skin', 'DictionaryList.html');
        }
        parent::render($mode);
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

    // NOTE: codice uguale al componente timeline
    private function formatDate($date)
    {
        $date = preg_replace('/^(\d{1,2})\/(\d{1,2})\/(-?\d{2,4})$/', '$3/$2/$1', $date);
        $date = preg_replace('/^(\d{1,2})\/(-?\d{2,4})$/', '$2', $date);
        $date = preg_replace('/^(-?\d{2,4})$/', '$1', $date);
        $date = str_replace('-', ',', $date);
        return str_replace('/', ',', $date);
    }
}
