<?php
// questo controller serve per gestire il campo di tipologia del campo Documents
// che è passato da un campo di picker sull'entià
// ad un campo per selezionare le pagine ed i contenuti del cms
//
class movio_modules_storyteller_controllers_ajax_PagePicker extends org_glizy_mvc_core_CommandAjax
{
    function execute($term, $id, $protocol, $filterType)
    {
        $this->directOutput = true;

        $speakingUrlManager = $this->application->retrieveProxy('org.glizycms.speakingUrl.Manager');
        if ($id && !$term) {
            $result = array();
            $json = json_decode($id);
            $id  = is_array($json) ? $json : array($id);
            foreach($id as $v) {
                if (is_object($v)) {
                    $v = 'movioContent:'.$v->id;
                }
                $tempResult = $speakingUrlManager->searchDocumentsByTerm('', $v, $protocol, $filterType);
                if ($tempResult && is_array($tempResult)) {
                    $result[] = $tempResult[0];
                }
            }

        } else {
            $result = $speakingUrlManager->searchDocumentsByTerm($term, '', $protocol, $filterType);

        }
        return $result;
    }
}