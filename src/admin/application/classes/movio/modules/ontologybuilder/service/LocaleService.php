<?php
class movio_modules_ontologybuilder_service_LocaleService extends GlizyObject
{
    protected $locale = array();

	public function __construct()
	{
	    $this->init();
	}

    public function init()
    {
        $options = array(
			'cacheDir' => org_glizy_Paths::get('CACHE_CODE'),
			'lifeTime' => -1,
			'readControlType' => '',
			'fileExtension' => '.php'
		);
		$cacheObj = &org_glizy_ObjectFactory::createObject('org.glizy.cache.CacheFile', $options);
		$cacheFileName = $cacheObj->verify(get_class( $this ));

		if ( $cacheFileName === false ) {
			$this->rebuildLocale();
            $cacheObj->save( serialize( $this->locale ), NULL, get_class( $this ) );
			$cacheObj->getFileName();
		} else {
			$this->locale = unserialize( file_get_contents( $cacheFileName ) );
		}
    }
    
    public function invalidate()
    {
        org_glizy_cache_CacheFile::cleanPHP();
        org_glizy_cache_CacheFile::cleanPHP(__Paths::get( 'BASE' ).'cache/');
        $this->init();
    }

    function onRegister()
    {
    }
    
    public function getTranslation($lang, $key)
    {
        $translation = $this->locale[$lang][$key];
        
        if (strpos($key, 'rel:') === 0) {
            return $translation ? $translation : str_replace('rel:', '', $key);
        } else {
            return $translation ? $translation : $key;
        }
    }
    
    public function keyAlreadyExists($lang, $key)
    {
        $languageCodes = $this->getLanguageCodes();

        foreach ($languageCodes as $languageCode) {
            if ($languageCode != $lang && $this->locale[$languageCode][$key]) {
                return $languageCode;
            }
        }
        
        return false;
    }
    
    private function getLanguageCodes()
    {
        $it = org_glizy_objectFactory::createModelIterator('movio.modules.ontologybuilder.models.Languages', 'all');

        $codes = array();

        foreach($it as $ar) {
            $codes[] = $ar->language_code;
        }

        return $codes;
    }

    public function rebuildLocale()
    {
        $it = org_glizy_objectFactory::createModelIterator('movio.modules.ontologybuilder.models.RelationTypesDocument');

        $languageCodes = $this->getLanguageCodes();

        $locale = array();

        foreach ($it as $ar) {
            foreach ($languageCodes as $languageCode) {
                if (@$ar->translation) {
                    $tr = $ar->translation[$languageCode];
                    if ($tr) {
                        $locale[$languageCode]['rel:'.$ar->key] = $tr;
                    }
                }
            }
        }

        $it = org_glizy_objectFactory::createModelIterator('movio.modules.ontologybuilder.models.EntityLabelsDocument', 'all');

        foreach ($it as $ar) {
            foreach ($languageCodes as $languageCode) {
                $tr = $ar->translation[$languageCode];
                if (@$ar->translation) {
                    if ($tr) {
                        $locale[$languageCode][$ar->key] = $tr;
                    }
                }
            }
        }

        foreach ($languageCodes as $languageCode) {
            $out  = '<?php'.PHP_EOL;

            if (!empty($locale[$languageCode])) {
                $out .= '$strings = '.var_export($locale[$languageCode], true).';'.PHP_EOL;
                $out .= 'org_glizy_locale_Locale::append($strings);';
            }

            file_put_contents(movio_modules_ontologybuilder_Paths::getLocalePath($languageCode), $out);
        }
        
        $this->locale = $locale;
    }
}