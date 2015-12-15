<?php
class movio_modules_thesaurus_controllers_dictionaryEdit_ajax_Save extends org_glizy_mvc_core_CommandAjax
{
    public function execute($data)
    {
        $this->directOutput = true;

        try {
            $data = json_decode($data);
            $thesaurusProxy = org_glizy_ObjectFactory::createObject('movio.modules.thesaurus.models.proxy.ThesaurusProxy');
            $termVO = $thesaurusProxy->getTermVO($data->type);
            $termVO->setFromObject($data);
            $thesaurusProxy->saveTerm($termVO);
        } catch (Exception $e) {
            return array('errors' => array($e->getMessage()));
        }
        return true;
    }
}