<?php
class org_glizycms_contents_services_PageTypeService extends GlizyObject
{
    protected $source = null;
    protected $pageTypesMap = array();

	public function __construct()
	{
		$this->source = __Paths::get('APPLICATION').'config/pageTypes.xml';

        if (file_exists($this->source)) {
    		$options = array(
    			'cacheDir' => org_glizy_Paths::get('CACHE_CODE'),
    			'lifeTime' => -1,
    			'readControlType' => '',
    			'fileExtension' => '.php'
    		);
    		$cacheObj = &org_glizy_ObjectFactory::createObject('org.glizy.cache.CacheFile', $options );
    		$cacheFileName = $cacheObj->verify( $this->_source, get_class( $this ) );

    		if ( $cacheFileName === false )
    		{
    			$this->loadXml();
    			$cacheObj->save( serialize( $this->pageTypesMap ), NULL, get_class( $this ) );
    			$cacheObj->getFileName();
    		}
    		else
    		{
    			$this->pageTypesMap = unserialize( file_get_contents( $cacheFileName ) );
            }
		}
	}

    function onRegister() {

    }

    private function loadXml() {
        $xml = org_glizy_ObjectFactory::createObject('org.glizy.parser.XML');
        $xml->loadAndParseNS($this->source);
        $pageTypes = $xml->getElementsByTagName('pageType');

        $this->pageTypesMap = array();

        foreach ($pageTypes as $pageType) {
            $name = $pageType->getAttribute('name');
            $this->pageTypesMap[$name] = array (
                                        'name' => $name,
                                        'label' => $pageType->hasAttribute('label') ? __T($pageType->getAttribute('label')) : $pageType->getAttribute('name'),
                                        'class' => $pageType->getAttribute('class'),
                                        'unique' => $pageType->hasAttribute('unique') ? $pageType->getAttribute('unique') == 'true' : false,
                                        'acceptParent' => $pageType->hasAttribute('acceptParent') ? $pageType->getAttribute('acceptParent') : ''
                                    );
        }
    }

    public function getAllPageTypes()
    {
        return $this->pageTypesMap;
    }
}