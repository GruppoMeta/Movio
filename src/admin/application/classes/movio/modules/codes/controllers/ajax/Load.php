<?php
class movio_modules_codes_controllers_ajax_Load extends org_glizy_mvc_core_CommandAjax
{
    function execute()
    {
        $this->checkPermissionForBackend();
        $it = org_glizy_objectFactory::createModelIterator('movio.modules.codes.models.Model');
            
        $result = array(
            'items' => array()
        );
        
        foreach($it as $ar) {
            $id = $ar->getId();
            
            $item = array(
                'id' => $id,
                'description' => $ar->custom_code_mapping_description,
                'code' => $ar->custom_code_mapping_code,
                'resourceLink' => $ar->custom_code_mapping_link,
            );
            
            if ($ar->custom_code_mapping_link) {
                $speakingUrlManager = $this->application->retrieveProxy('org.glizycms.speakingUrl.Manager');
                $docs = $speakingUrlManager->searchDocumentsByTerm('', $ar->custom_code_mapping_link);
                $item['resourceLinkLabel'] = $docs[0]['text'];
                $item['downloadQrCode'] = __Link::makeUrl('actionsMVC',  array('action' => 'makeQRCode', 'id' => $id));
            }
            
            $result['items'][] = $item;
        }
        
        return $result;
    }
}
?>