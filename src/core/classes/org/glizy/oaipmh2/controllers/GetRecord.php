<?php
class org_glizy_oaipmh2_controllers_GetRecord extends org_glizy_rest_core_CommandRest
{
    use \org_glizy_oaipmh2_core_SetHelperTrait;
    use \org_glizy_oaipmh2_core_ParamsTrait;
    use \org_glizy_oaipmh2_core_XmlOutputTrait;

    /**
     * @return string
     */
    function execute()
    {
        $identifier = $this->getParam('identifier', true, org_glizy_oaipmh2_core_ParamsType::TYPE_GENERIC);
        $metadataSets = $this->getParam('metadataPrefix', true, org_glizy_oaipmh2_core_ParamsType::TYPE_METADATA_PREFIX);

        $adapter = $this->application->getAdapter();
        /** @var org_glizy_oaipmh2_models_VO_IdentifierVO $identifierVO */
        $identifierVO = $adapter->parseIdentifier($identifier);
        /** @var org_glizy_oaipmh2_core_SetInterface $set */
        $set = $this->getSet($metadataSets, $identifierVO->setSpec);
        $setInfo = $set->getSetInfo();
        /** @var org_glizy_oaipmh2_models_VO_RecordVO $recordVO */
        $recordVO = $adapter->findById($set->getSetInfo(), $identifierVO);

        $output =  '<record>'.
                        '<header'.($recordVO->deleted ? ' status="deleted"' : '').'>'.
                            '<identifier>'.$this->encodeXmlText($identifier).'</identifier>'.
                            '<datestamp>'.$this->encodeXmlText($this->formatDatestamp($recordVO->datestamp)).'</datestamp>'.
                            '<setSpec>'.$this->encodeXmlText($setInfo['setSpec']).'</setSpec>'.
                        '</header>'.
                        '<metadata>'.
                            $set->getRecord($recordVO).
                        '</metadata>'.
                    '</record>';

        return $output;
    }

}
