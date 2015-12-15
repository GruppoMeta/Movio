<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_components_CSS extends org_glizy_components_Component
{
	/**
	 * Init
	 *
	 * @return	void
	 * @access	public
	 */
	function init()
	{
		parent::init();

		// define the custom attributes
		$this->defineAttribute('src', false, NULL, COMPONENT_TYPE_STRING);
		$this->defineAttribute('folder', false, NULL, COMPONENT_TYPE_STRING);
		$this->defineAttribute('inline', false, false, COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('editableRegion', 	false, 	'head', COMPONENT_TYPE_STRING);
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
        $region = $this->getAttribute('editableRegion');
		if ( $folder )
		{
			// include tutta una cartella
			$cssFileName = $this->includeFolder( $folder );
			if ( $this->getAttribute('inline')) {
				$css = file_get_contents($cssFileName);
				if ( strpos($css, '<style') !== false ) {
					$this->addOutputCode( $css, $region );
				} else {
					$this->addOutputCode( org_glizy_helpers_CSS::CSScode( $css ), $region );
				}
			} else {
				$this->addOutputCode( org_glizy_helpers_CSS::linkCSSfile( $cssFileName), $region );
			}
		}
		else
		{
			if (!is_null($src))
			{
				$this->addOutputCode( org_glizy_helpers_CSS::linkCSSfile( $this->getAttribute('src') ), $region );
			}
			else
			{
				$this->addOutputCode( org_glizy_helpers_CSS::CSScode( $this->getText() ), $region );
			}
		}
	}

	private function includeFolder( $folder )
	{
		// controlla se il file in cache è valido
		$options = array(
			'cacheDir' => org_glizy_Paths::get('CACHE_CODE'),
			'lifeTime' => org_glizy_Config::get('CACHE_CODE'),
			'readControlType' => '',
			'fileExtension' => '.css'
		);

		$cacheSignature = get_class( $this ).$folder;
		$cacheObj = org_glizy_ObjectFactory::createObject( 'org.glizy.cache.CacheFile', $options );
		$cssFileName = $cacheObj->verify( $cacheSignature );

		if ($cssFileName===false)
		{
			$cssFile = '';

			if ($dir_handle = @opendir($folder))
			{
				while ($file_name = readdir($dir_handle))
				{
					if ($file_name!="." &&
						$file_name!=".." &&
						!is_dir("$dir/$file_name") )
					{
						$f[]=$file_name;
					}
				}
				sort($f);
				closedir($dir_handle);
				foreach ($f as $element)
				{
					$cssCode = file_get_contents( $folder.'/'.$element );
					$cssFile .= $cssCode."\n";
				}
			}

			if ( __Config::get( 'DEBUG' ) !== false )
			{
				$cacheObj->save( $cssFile, NULL, get_class($this) );
			}
			else
			{
				$cacheObj->save( $cssFile, NULL, $cacheSignature );
			}
			$cssFileName = $cacheObj->getFileName();
		}
		return $cssFileName;
	}
}