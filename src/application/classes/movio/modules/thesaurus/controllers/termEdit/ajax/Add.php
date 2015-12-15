<?php
class movio_modules_thesaurus_controllers_termEdit_ajax_Add extends org_glizy_mvc_core_CommandAjax
{
    public function execute($data)
    {
        $this->directOutput = true;
        try {
            $data = json_decode($data);
            $parentId = $data->parentId ?: $data->dictionaryId;
            $thesaurusProxy = org_glizy_ObjectFactory::createObject('movio.modules.thesaurus.models.proxy.ThesaurusProxy');
            $termVO = $thesaurusProxy->getTermVO($data->type);
            $termVO->setFromObject($data);
            $arTerm = $thesaurusProxy->saveTerm($termVO);
        } catch (Exception $e) {
            return array('errors' => array($e->getMessage()));
        }

        return array(
            'evt' => 'thesaurus.termAdded',
            'message' => array('termId' => $arTerm->getId(), 'parentId' => $parentId)
        );
    }
}