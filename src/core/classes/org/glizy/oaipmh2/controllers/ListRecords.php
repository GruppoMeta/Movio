<?php
class org_glizy_oaipmh2_controllers_ListRecords extends org_glizy_oaipmh2_controllers_ListIdentifiers
{
    use \org_glizy_oaipmh2_core_SetHelperTrait;
    use \org_glizy_oaipmh2_core_ParamsTrait;
    use \org_glizy_oaipmh2_core_ResumptionTokenTrait;
    use \org_glizy_oaipmh2_core_XmlOutputTrait;

    /**
     * @param string $identifier
     * @param org_glizy_oaipmh2_models_VO_RecordVO $recordVO
     * @param org_glizy_oaipmh2_core_SetInterface $set
     * @return string
     */
    protected function makeResult($identifier, org_glizy_oaipmh2_models_VO_RecordVO $recordVO, org_glizy_oaipmh2_core_SetInterface $set)
    {
        $output .=  '<record>'.
                        parent::makeResult($identifier, $recordVO, $set).
                        '<metadata>'.
                            $set->getRecord($recordVO).
                        '</metadata>'.
                    '</record>';

        return $output;
    }


}
