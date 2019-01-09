<?php
class org_glizy_oaipmh2_controllers_ListMetadataFormats extends org_glizy_rest_core_CommandRest
{
    use \org_glizy_oaipmh2_core_XmlOutputTrait;

    /**
     * @return string
     */
    function execute()
    {
        $output = '';
        $metadataFormats = $this->application->getMetadataFormat();
        /** @var org_glizy_oaipmh2_models_VO_MetadataVO $metadataVO */
        foreach($metadataFormats as $metadataVO) {
            $output .= '<metadataFormat>'.
                            '<metadataPrefix>'.$this->encodeXmlText($metadataVO->prefix).'</metadataPrefix>'.
                            '<schema>'.$this->encodeXmlText($metadataVO->schema).'</schema>'.
                            '<metadataNamespace>'.$this->encodeXmlText($metadataVO->namespace).'</metadataNamespace>'.
                        '</metadataFormat>';
        }

        return $output;
    }
}
