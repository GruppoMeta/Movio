<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizy_components_ImageExternal extends org_glizy_components_Component
{
	var $imageInfo;
	var $media;
	var $mediaUrl;


	/**
	 * Init
	 *
	 * @return	void
	 * @access	public
	 */
	function init()
	{
		// define the custom attributes
		$this->defineAttribute('cssClass',		false, 	'',		COMPONENT_TYPE_STRING);
		$this->defineAttribute('crop',			false, 	false,	COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('cropOffset',	false, 	0,		COMPONENT_TYPE_STRING);
		$this->defineAttribute('height',		false, 	NULL,	COMPONENT_TYPE_INTEGER);
		$this->defineAttribute('label',			false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('width',			false, 	NULL,	COMPONENT_TYPE_INTEGER);
		$this->defineAttribute('group',			false, '',	COMPONENT_TYPE_STRING);
		$this->defineAttribute('checkIfExists',			false, true,	COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('path',			false, true,	COMPONENT_TYPE_STRING);

		// call the superclass for validate the attributes
		parent::init();
	}


	/**
	 * Process
	 *
	 * @return	boolean	false if the process is aborted
	 * @access	public
	 */
	function process()
	{
		$this->mediaUrl = $this->_parent->loadContent($this->getId());
		$this->resetContent();
		if ( !empty( $this->mediaUrl ) )
		{
			$this->attachMedia();
		}

		$this->processChilds();
	}

	function getContent($parent=NULL)
	{
		$this->_render_html();
		return $this->_content;
	}

	/**
	 * Render
	 *
	 * @return	void
	 * @access	public
	 */
	function render_html()
	{
		if (!is_null($parent) && $parent=='glz:RecordSetList')
		{
			// elimina alcuni attributi
			// perch� l'immagine � visualizzata all'interno di un recordsetlist
			unset($this->_content['zoom']);
			unset($this->_content['onclick']);
			$this->setAttribute( 'zoom', false );
		}

		$this->_render_html();
		$this->addOutputCode($this->_content['__html__']);
	}

	function _render_html()
	{
		if ( !empty( $this->mediaUrl ) )
		{
			$attributes = $this->_content;

			if (is_string($this->mediaUrl) && (!is_null($this->getAttribute('width')) || !is_null($this->getAttribute('height')))) {
			    if (strpos( $this->mediaUrl, 'http://') === 0 or strpos( $this->mediaUrl, 'https://') === 0) {
    			    $extension = pathinfo(parse_url($this->mediaUrl, PHP_URL_PATH), PATHINFO_EXTENSION);
    			    $cacheFileName = 'external_'.md5($this->mediaUrl).'.'.$extension;
    			    $cacheFilePath = __Paths::get('CACHE').$cacheFileName;

    			    // scarica il file e lo mette in cache
    			    if (!file_exists($cacheFilePath)) {
    			        @file_put_contents($cacheFilePath, @file_get_contents($this->mediaUrl));
    			    }

    			    $params = array('media_id' => 0, 'media_fileName' => $cacheFilePath);
    			    $this->media = __ObjectFactory::createObject('org.glizycms.mediaArchive.media.Image', $params);
			    }

				$this->media->allowDownload = true;
			    $thumbnail = $this->media->getResizeImage($this->getAttribute('width'), $this->getAttribute('height'), $this->getAttribute('crop'), $this->getAttribute('cropOffset'), false, false);

				$attributes['src'] 				= $thumbnail['fileName'];
				$attributes['width'] 			= $thumbnail['width'];
				$attributes['height'] 			= $thumbnail['height'];
				$this->_content['src'] 			= $thumbnail['fileName'];
				$this->_content['width'] 		= $thumbnail['width'];
				$this->_content['height'] 		= $thumbnail['height'];
			}

			unset($attributes['mediaUrl']);
			unset($attributes['zoom']);
			unset($attributes['size']);
			unset($attributes['mediaType']);
			$this->_content['__html__'] = '<img '.$this->_renderAttributes($attributes).'/>';

			if ( $this->getAttribute( 'zoom' ) || $useZoom )
			{
				$this->_application->addLightboxJsCode();
				$attributes = array();
				$attributes['title'] = $this->_content['title'];
				$thumbnail = $this->media->getResizeImage( __Config::get( 'IMG_WIDTH_ZOOM' ), __Config::get( 'IMG_HEIGHT_ZOOM' ) );
				$attributes['href'] = $thumbnail['fileName'];
				$attributes['rel'] = 'milkbox'.( $this->getAttribute( 'group' ) != '' ? '['.$this->getAttribute( 'group' ).']' : '' ) ;
				$attributes['class'] = $zoomCssClass;
				$this->_content['__html__'] = org_glizy_helpers_Html::renderTag( 'a', $attributes, true, $this->_content['__html__'] );
			}
		}
		else
		{
			$this->_content['__html__'] = '';
		}
	}


	function resetContent()
	{
		$this->_content = array();
		$this->_content['mediaUrl']			= '';
		$this->_content['src'] 				= '';
		$this->_content['alt'] 				= '';
		$this->_content['title'] 			= '';
		$this->_content['class'] 			= '';
		$this->_content['style'] 			= '';
		$this->_content['size'] 			= '';
		$this->_content['mediaType'] 			= '';
	}

	function attachMedia()
	{
		$this->_content = array();
		$this->_content['mediaUrl']		= $this->mediaUrl;
		$this->_content['alt'] 			= $this->mediaUrl;
		$this->_content['title'] 		= $this->mediaUrl;
		$this->_content['class'] 		= $this->getAttribute('cssClass');
		$this->_content['style'] 		= "";

		if ( is_string($this->mediaUrl) && strpos( $this->mediaUrl, 'http://') === false && strpos( $this->mediaUrl, 'https://') === false )
		{
			$this->media = &org_glizycms_mediaArchive_MediaManager::getEmptyMediaByType( 'IMAGE' );
			$this->media->fileName = $this->getAttribute( 'path' ).'/'.$this->mediaUrl;
			$this->imageInfo = $this->media->getImageInfo();
			$this->_content['src'] 			= $this->media->getFileName( $this->getAttribute( 'checkIfExists' ) );
			$this->_content['width'] 		= $this->imageInfo['width'];
			$this->_content['height'] 		= $this->imageInfo['height'];
		}
		else
		{
			$this->_content['src'] 			= $this->mediaUrl;
			$this->_content['width'] 		= "";
			$this->_content['height'] 		= "";
		}
	}
}