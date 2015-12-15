<?php
class org_glizycms_contents_controllers_autocomplete_ajax_PagePicker extends org_glizy_mvc_core_CommandAjax
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