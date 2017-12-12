<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_components_JSscript extends org_glizy_components_Component
{
	/**
	 * Init
	 *
	 * @return	void
	 * @access	public
	 */
	function init()
	{
		// define the custom attributes
		$this->defineAttribute('src', false, NULL, COMPONENT_TYPE_STRING);
		$this->defineAttribute('folder', false, NULL, COMPONENT_TYPE_STRING);
		$this->defineAttribute('type', false, 'text/javascript', COMPONENT_TYPE_STRING);
		$this->defineAttribute('inline', false, false, COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('editableRegion', 	false, 	'head', COMPONENT_TYPE_STRING);
		$this->defineAttribute('extension', false, 'js', COMPONENT_TYPE_STRING);
		$this->defineAttribute('minify', false, !__Config::get('DEBUG'), COMPONENT_TYPE_BOOLEAN);

		parent::init();
	}

	/**
	 * Render
	 *
	 * @return	void
	 * @access	public
	 */
	function render_html()
	{
		$folder = $this->getAttribute('folder');
		$src = $this->getAttribute('src');
		$type = $this->getAttribute('type');

        $language = $this->_application->getLanguage();
        $language2 = $language.'-'.strtoupper($language);
        $src = str_replace(array('##LANG##', '##LANG2##'), array($language, $language2), $src);

		if ( $folder )
		{
			if (!org_glizy_ObjectValues::get('org.glizy.JS', 'run', false))
			{
				org_glizy_ObjectValues::set('org.glizy.JS', 'run', true);
				$pageType = $this->_application->getPageType();
				$state = __Request::get( 'action', '' );
				$params = @$_GET;
				$params['id'] = __Request::get('id', '');

				$params = json_encode($params);
				$jsCode = <<<EOD
var GlizyApp = {};
GlizyApp.pages = {};
jQuery( function(){
	if ( typeof( GlizyApp.pages[ '$pageType' ] ) != 'undefined' )
	{
		GlizyApp.pages[ '$pageType' ]( '$state', $params );
	}
})
EOD;

				$this->addOutputCode( org_glizy_helpers_JS::JScode( $jsCode ), 'head' );
			}

			// include tutta una cartella
			$jsFileName = $this->includeFolder( $folder, $language);
			if ( $this->getAttribute('inline')) {
				$js = file_get_contents($jsFileName);
				if ( strpos($js, '<script') !== false ) {
					$this->addOutputCode( $js );
				} else {
					$this->addOutputCode( org_glizy_helpers_JS::JScode( $js, $type ) );
				}
			} else {
				$minify = $this->getAttribute('minify');
				$this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $jsFileName.(!$minify ? '?'.microtime(true) : '') , null, $type) );
			}
		}
		else
		{
			if ($src)
			{
				$this->addOutputCode( org_glizy_helpers_JS::linkJSfile( $src, null, $type ) );
			}
			else
			{
				$this->addOutputCode( org_glizy_helpers_JS::JScode( $this->replaceLocale($this->getText()), $type ) );
			}
		}
	}

	private function includeFolder( $folder, $language )
	{
		// controlla se il file in cache è valido
		$options = array(
			'cacheDir' => org_glizy_Paths::get('CACHE_JS'),
			'lifeTime' => org_glizy_Config::get('CACHE_CODE'),
			'readControlType' => '',
			'fileExtension' => '.js'
		);

		$cacheSignature = get_class( $this ).$folder.$language;
		$cacheObj = org_glizy_ObjectFactory::createObject( 'org.glizy.cache.CacheFile', $options );
		$jsFileName = $cacheObj->verify( $cacheSignature );
		if ($jsFileName===false)
		{
			$jsFile = '';
			$folder = glz_findClassPath($folder);
			$extension = $this->getAttribute('extension');

			foreach(glob($folder.'/*'.$extension) as $file) {
	            // $file = pathinfo($file);
	            // $file = str_replace('_', '.', $file['filename']);
	            // $availableModules[] = $file;
	            // $f[] = $file;
	            $jsCode = file_get_contents($file);
				$jsCode = $this->replaceLocale($jsCode);
				$jsFile .= $jsCode."\n";
	        }

			// NOTE: necesssario per la macchina vagrant
            // per problemi di sincronizzazione del file
            @unlink($cacheObj->getFileName());

	        $minify = $this->getAttribute('minify');
			if ( !$minify || $this->getAttribute('inline'))
			{
				$cacheObj->save( $jsFile, NULL, get_class($this) );
			}
			else
			{
				require_once (org_glizy_Paths::get('CORE_LIBS').'/jsmin/jsmin.php');
				$cacheObj->save( JSMin::minify( $jsFile ), NULL, $cacheSignature );
			}
			$jsFileName = $cacheObj->getFileName();
		}
		return $jsFileName;
	}

	private function replaceLocale($text)
	{
	    preg_match_all('/(\{)((i18n:)([^(\'"\})]*))(\})/', $text, $matches, PREG_OFFSET_CAPTURE);
	    if (count($matches[0])) {
	        for ($i=count($matches[0])-1; $i>=0;$i--) {
	            $text = str_replace($matches[0][$i][0], __Tp($matches[4][$i][0]), $text);
	        }
	    }

	    preg_match_all('/(\{)((config:)([^(\}]*))(\})/', $text, $matches, PREG_OFFSET_CAPTURE);
	    if (count($matches[0])) {
	        for ($i=count($matches[0])-1; $i>=0;$i--) {
	            $text = str_replace($matches[0][$i][0], __Config::get($matches[4][$i][0]), $text);
	        }
	    }
	    return $text;
	}
}
