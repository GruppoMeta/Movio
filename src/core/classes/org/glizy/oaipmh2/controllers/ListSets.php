<?php
class org_glizy_oaipmh2_controllers_ListSets extends org_glizy_rest_core_CommandRest
{
    use \org_glizy_oaipmh2_core_SetHelperTrait;
    use \org_glizy_oaipmh2_core_XmlOutputTrait;

    /**
     * @return    string
     */
    function execute()
    {
        $output = '';
        $sets = $this->flatten($this->application->getSets());
        if (!count($sets)) {
            throw org_glizy_oaipmh2_core_Exception::noSetHierarchy();
        }

        $metadataFormats = $this->application->getMetadataFormat();
        /** @var org_glizy_oaipmh2_models_VO_MetadataVO $oaidcFornat */
        $oaidcFornat = $metadataFormats['oai_dc'];

        /** @var org_glizy_oaipmh2_core_SetInterface $setClass */
        foreach($sets as $setClass) {
            $setDescription = '';
            $info = $setClass->getSetInfo();
            if ( !empty( $info[ 'setDescription' ] ) ) {
                $setDescription = '<setDescription>'.
                                    $this->openMetadataHeader($oaidcFornat).
                                    '<dc:description>'.$this->encodeXmlText($info['setDescription']).'</dc:description>'.
                                    '<dc:creator>'.$this->encodeXmlText($info['setCreator']).'</dc:creator>'.
                                    $this->closeMetadataHeader($oaidcFornat).
                                  '</setDescription>';
            }

            $output .= '<set>'.
                        '<setSpec>'.$this->encodeXmlText($info[ 'setSpec' ] ).'</setSpec>'.
                        '<setName>'.$this->encodeXmlText($info[ 'setName' ] ).'</setName>'.$setDescription.
                       '</set>';

        }


        return $output;
    }
}
