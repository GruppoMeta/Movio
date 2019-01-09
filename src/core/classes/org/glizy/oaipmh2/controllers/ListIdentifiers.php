<?php
class org_glizy_oaipmh2_controllers_ListIdentifiers extends org_glizy_rest_core_CommandRest
{
    use \org_glizy_oaipmh2_core_SetHelperTrait;
    use \org_glizy_oaipmh2_core_ParamsTrait;
    use \org_glizy_oaipmh2_core_ResumptionTokenTrait;
    use \org_glizy_oaipmh2_core_XmlOutputTrait;

    /**
     * @return string
     */
    function execute()
    {
        /** @var org_glizy_oaipmh2_models_VO_ResumptionInfoVO $resumptionInfoVO */
        $resumptionInfoVO = $this->resumptionInfoOrParams();
        $limitLength = __Config::get('oaipmh.maxIds');

        $output = '';
        $modelsMap = $this->modelsMap($resumptionInfoVO->metadataSets);
        /** @var org_glizy_oaipmh2_core_AdapterInterface $adapter */
        $adapter = $this->application->getAdapter();

        $sets = $resumptionInfoVO->metadataSets;
        if ($resumptionInfoVO->set) {
            $sets = array_filter($sets, function($item) use ($resumptionInfoVO){
                $setInfo = $item->getSetInfo();
                return $setInfo['setSpec'] == $resumptionInfoVO->set;
            });

            if (!count($sets)) {
                throw org_glizy_oaipmh2_core_Exception::cannotDisseminateFormat($resumptionInfoVO->set);
            }
        }


        /** @var org_glizy_oaipmh2_models_VO_ListVO $result */
        $result = $adapter->findAll($sets, $resumptionInfoVO->from, $resumptionInfoVO->until, $resumptionInfoVO->limitStart, $limitLength);
        
        /** @var org_glizy_oaipmh2_models_VO_RecordVO $doc */
        foreach ($result->records as $doc) {
            $output .= $this->makeResult($adapter->createIdentifier($modelsMap[$doc->setSpec], $doc->id), $doc, $modelsMap[$doc->setSpec]);
        }

        $resumptionInfoVO->prefix = 'ListIdentifiers';
        $resumptionInfoVO->numRows = $result->numRows;
        $resumptionInfoVO->limitEnd = $resumptionInfoVO->limitStart + $limitLength;

        return $this->createResumptionToken($resumptionInfoVO).$output;
    }

    /**
     * @param string $identifier
     * @param org_glizy_oaipmh2_models_VO_RecordVO $recordVO
     * @param org_glizy_oaipmh2_core_SetInterface $set
     * @return string
     */
    protected function makeResult($identifier, org_glizy_oaipmh2_models_VO_RecordVO $recordVO, org_glizy_oaipmh2_core_SetInterface $set)
    {
        $setInfo = $set->getSetInfo();
        $deletedAttribute = $recordVO->deleted ? ' status="deleted"' : '';
        $output  = '<header'.$deletedAttribute.'>'.
                        '<identifier>'.$this->encodeXmlText($identifier).'</identifier>'.
                        '<datestamp>'.$this->encodeXmlText($this->formatDatestamp($recordVO->datestamp)).'</datestamp>'.
                        '<setSpec>'.$this->encodeXmlText($setInfo['setSpec']).'</setSpec>'.
                    '</header>';

        return $output;
    }


}
