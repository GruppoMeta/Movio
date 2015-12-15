<?php
class movio_modules_thesaurus_controllers_dictionaryEdit_ajax_Add extends org_glizy_mvc_core_CommandAjax
{
    public function execute($data)
    {
        $this->directOutput = true;

        try {
            $data = json_decode($data);
            $thesaurusProxy = org_glizy_ObjectFactory::createObject('movio.modules.thesaurus.models.proxy.ThesaurusProxy');
            $arTerm = $thesaurusProxy->addDictionary($data->title, $data->type);
            return array('url' => $this->changeAction( 'editDictionary/?&dictionaryId='.$arTerm->document_id ));
        } catch (Exception $e) {
            return array('errors' => array($e->getMessage()));
        }
    }
}