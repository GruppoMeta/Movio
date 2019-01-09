<?php
class org_glizy_oaipmh2_core_Application extends org_glizy_mvc_core_Application
{
    private $adapter;

    private $metadataFormat = [];
    private $sets = [];

	public function _init()
    {
		parent::_init();

        $this->addMetadataFormat( org_glizy_oaipmh2_models_VO_MetadataVO::create('oai_dc',
                                'http://www.openarchives.org/OAI/2.0/oai_dc.xsd',
                                'http://www.openarchives.org/OAI/2.0/oai_dc/',
                                'dc',
                                'http://purl.org/dc/elements/1.1/'));
    }


    public function _startProcess()
	{
        try {
            $verb = $this->verbFromRequest();
            $controllerClass = org_glizy_ObjectFactory::createObject('org.glizy.oaipmh2.controllers.'.$verb, $this);
            $result = org_glizy_helpers_PhpScript::callMethodWithParams($controllerClass, 'execute');
        } catch (Exception $e) {
            if (!is_a($e, 'org_glizy_oaipmh2_core_Exception')) {
                throw $e;
            }
            $verb = '';
            $result = (string)$e;
        }

        header("Content-Type: text/xml; charset=".GLZ_CHARSET);
        echo $this->formatOutput($verb, $result, $this->queryStringToAttributes());
    }

    /**
     * @param string|org_glizy_oaipmh2_core_AdapterInterface $classPath
     * @return void
     */
    public function setAdapter($classPath)
    {
        $adapter = is_object($classPath) ? $classPath : org_glizy_ObjectFactory::createObject($classPath);
        if (!$adapter) {
            throw org_glizy_oaipmh2_core_Exception::noAdapter();
        }

        $this->adapter = $adapter;
    }

    /**
     * @return org_glizy_oaipmh2_core_AdapterInterface
     */
    public function getAdapter()
    {
       return $this->adapter;
    }


    /**
     * @param org_glizy_oaipmh2_models_VO_MetadataVO $metadataVO
     * @return void
     */
    public function addMetadataFormat(org_glizy_oaipmh2_models_VO_MetadataVO $metadataVO)
	{
		$this->metadataFormat[$metadataVO->prefix] = $metadataVO;
	}

    /**
     * @return org_glizy_oaipmh2_models_VO_MetadataVO[]
     */
	public function getMetadataFormat()
	{
		return $this->metadataFormat;
    }

    /**
     * @param string $metadataPrexif
     * @param string|Class $classPath
     */
	public function addSet($metadataPrexif, $classPath)
	{
        $setClass = is_object($classPath) ? $classPath : org_glizy_ObjectFactory::createObject($classPath);
        if (!$setClass) {
            throw org_glizy_oaipmh2_core_Exception::genericError('Set class does not exist: '.$classPath);
        }

        if (!isset($this->sets[$metadataPrexif])) {
            $this->sets[$metadataPrexif] = [];
        }

		$this->sets[$metadataPrexif][] = $setClass;
	}

    /**
     * @param  string $metadataPrexif
     * @return string[]
     */
	public function getSet($metadataPrexif)
	{
        if (!isset($this->sets[$metadataPrexif])) {
            throw org_glizy_oaipmh2_core_Exception::cannotDisseminateFormat($metadataPrexif);
        }

		return $this->sets[$metadataPrexif];
    }


    /**
     * @return string[]
     */
    function getSets()
	{
		return $this->sets;
    }

    /**
     * @return string
     */
    private function verbFromRequest()
    {
        $validVerbs = ['GetRecord', 'Identify', 'ListIdentifiers', 'ListMetadataFormats', 'ListRecords', 'ListSets'];
        $verb = __Request::get('verb');

        if (!in_array($verb, $validVerbs)) {
            throw org_glizy_oaipmh2_core_Exception::noVerb();
        }

        return $verb;
    }

    /**
     * @param string $content
     * @param string $requestAttribs
     * @return string
     */
    private function formatOutput($verb, $content, $requestAttribs)
    {
        $responseDate = gmstrftime('%Y-%m-%dT%T').'Z';
        $requestUrl = org_glizy_Routing::scriptUrl(true);
        $openVerb = '';
        $closeVerb = '';

        if ($verb) {
            $openVerb = '<'.$verb.'>';
            $closeVerb = '</'.$verb.'>';
        }

        $charset = GLZ_CHARSET;
        $output = <<<EOD
<?xml version="1.0" encoding="$charset"?>
<OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/"
         xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/
         http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd">
        <responseDate>$responseDate</responseDate>
        <request $requestAttribs>$requestUrl</request>
        $openVerb
        $content
        $closeVerb
</OAI-PMH>
EOD;

        return $output;
    }

    /**
     * @return string
     */
    private function queryStringToAttributes()
    {
        $requestAttribs = [];
        $params = org_glizy_Request::_getValuesArray();
        foreach($params as $k=>$v) {
            if ($v[ GLZ_REQUEST_TYPE ] !== GLZ_REQUEST_GET || strpos($k, '__')!==false) continue;

            $requestAttribs[] = $k.'="'.htmlentities( $v[GLZ_REQUEST_VALUE] ).'"';
        }

        return implode(' ', $requestAttribs);
    }
}
