<?php
class movio_modules_ontologybuilder_service_FieldTypeService extends GlizyObject
{
    protected $source = null;
    protected $typeMap = array();

	public function __construct()
	{
		$this->source = __Paths::get('APPLICATION_TO_ADMIN').'config/fieldTypes.xml';

		$options = array(
			'cacheDir' => org_glizy_Paths::get('CACHE_CODE'),
			'lifeTime' => -1,
			'readControlType' => '',
			'fileExtension' => '.php'
		);
		$cacheObj = &org_glizy_ObjectFactory::createObject('org.glizy.cache.CacheFile', $options );
		$cacheFileName = $cacheObj->verify( $this->source, get_class( $this ) );

		if ( $cacheFileName === false )
		{
			$this->loadXml();
			$cacheObj->save( serialize( $this->typeMap ), NULL, get_class( $this ) );
			$cacheObj->getFileName();
		}
		else
		{
			$this->typeMap = unserialize( file_get_contents( $cacheFileName ) );
		}
	}

    function onRegister() {

    }

    private function loadXml() {
        $xml = org_glizy_ObjectFactory::createObject('org.glizy.parser.XML');
        $xml->loadAndParseNS($this->source);
        $fieldTypes = $xml->getElementsByTagName('fieldType');

        foreach ($fieldTypes as $fieldType) {
            $this->typeMap[$fieldType->getAttribute('id')] = $this->loadChildNodes($fieldType);
        }
    }

    private function loadChildNodes($xmlNode) {
        if ($xmlNode->childNodes->length == 1) {
            return $xmlNode->nodeValue;
        }
        else {
            $values = array();

            foreach($xmlNode->childNodes as $child) {
                $values[$child->nodeName] = $this->loadChildNodes($child);
            }

            return $values;
        }
    }

	public function getTypeMapping($type)
	{
        return $this->typeMap[$type]['map_to'];
	}

    public function getTypeTranslation($type, $language_code)
    {
        return $this->typeMap[$type]['translation'][$language_code];
	}

    public function isTypeIndexed($type)
    {
        return $this->typeMap[$type]['is_indexed'] == 'true';
	}

    public function getAllTypes()
    {
        return $this->typeMap;
    }
}