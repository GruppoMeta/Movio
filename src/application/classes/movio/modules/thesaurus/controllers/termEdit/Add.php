<?php
class movio_modules_thesaurus_controllers_termEdit_Add extends org_glizy_mvc_core_Command
{
    public function execute()
    {
        $dictionaryId = __Request::get('dictionaryId');
        $this->setComponentsAttribute('dictionaryId', 'value', $dictionaryId);
        $thesaurusProxy = org_glizy_ObjectFactory::createObject('movio.modules.thesaurus.models.proxy.ThesaurusProxy');
        $type = $thesaurusProxy->getTypeByDictionaryId($dictionaryId);
        $this->setComponentsAttribute('type', 'value', $type);
        $typeEnum = org_glizy_ObjectFactory::createObject('movio.modules.thesaurus.models.TermTypeEnum');
        $typeArray = $typeEnum::getTypes();
        $disabledPanel = array();
        foreach ($typeArray as $currentType)
        {
            if ($currentType!= $type) {
                $disabledPanel[] = $currentType.'Panel';
            }
        }
        $this->setComponentsVisibility( $disabledPanel , false );
    }

}