<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizycms_mediaArchive_media_Image extends org_glizycms_mediaArchive_media_Media
{
	var $_imageInfo = null;
	var $_cacheObj;
	var $_piramidImageSizes = array( array( 1024, 768 ), array( 512, 384 ), array( 256, 192 ) );

	function getOriginalImageInfo()
	{
		if ( file_exists( $this->getFileName() ) )
		{
			if (is_null($this->_imageInfo)) $this->_imageInfo = getImageSize($this->getFileName());
			return $this->_imageInfo;
		}
	}

	function getOriginalSizes()
	{
		$this->getOriginalImageInfo();
		return array('width' => $this->_imageInfo[0], 'height' => $this->_imageInfo[1]);
	}

	function getOriginalImageType()
	{
		$this->getOriginalImageInfo();
		return $this->_imageInfo['mime'];
	}

	function getImageInfo()
	{
		$sizes = $this->getOriginalSizes();
		$fileName = $this->getFileName();
		$filetype = $this->getOriginalImageType();
		return array('imageType' => $filetype, 'fileName' => $fileName, 'width'=> $sizes['width'], 'height'=>  $sizes['height']);
	}

	function preparePiraimdImages()
	{
		foreach ( $this->_piramidImageSizes as $v )
		{
			$this->getResizeImage( $v[ 0 ], $v[ 1 ], false, 1, false, true, true );
		}
	}

	function getResizeImage($width, $height, $crop=false, $cropOffset=1, $forceSize=false, $returnResizedDimension=true, $usePiramidalSizes = true)
	{
		// check the size
		$originalWidth = $width;
		$originalHeight = $height;
		$resize = true;
		$cacheFileName = $this->_verifyCache( $width, $height, $crop, $cropOffset, $forceSize );
		if ($cacheFileName!==false)
		{
			$useCache = true;
			if (!$this->allowDownload) {
				$returnResizedDimension = true;
			}

			if ( $returnResizedDimension )
			{
				list($width, $height) = getImageSize($cacheFileName);
				if (!$this->allowDownload) {
					$maxWidth = intval(__Config::get('IMG_DOWNLOAD_WIDTH'));
				    $maxHeight = intval(__Config::get('IMG_DOWNLOAD_HEIGHT'));
				    if ($maxWidth < $width || $maxHeight < $height) {
						$useCache = false;
				    }
				}
			}
			else
			{
				$width = NULL;
				$height = NULL;
			}

			if ($useCache) {
				return array('imageType' => IMG_JPG, 'fileName' => $cacheFileName, 'width' => $width, 'height' => $height, 'originalWidth'=> NULL, 'originalHeight'=>  NULL);
			}
		}

		$originalSizes = $this->getOriginalSizes();
		if ( $width == $originalSizes['width'] && $height == $originalSizes['height']) {
			$resize = false;
		}

		if ( !$resize && !$this->watermark)
		{
			copy( $this->getFileName(), $this->_cacheObj->getFileName() );
			@touch( $this->_cacheObj->getFileName(), filemtime($this->getFileName()));
			return array('imageType' => IMG_JPG, 'fileName' => $this->getFileName(), 'width' => $width, 'height' => $height, 'originalWidth'=> $width, 'originalHeight'=>  $height );
		}

		return $this->resizeImage($this->_cacheObj->getFileName(), $width, $height, $crop, $cropOffset, $forceSize, $usePiramidalSizes, $resize);
	}

	// NOTE: verificare la duplicazione del codice con getResizeImage
	function getThumbnail($width, $height, $crop=false, $cropOffset=1, $forceSize=false, $useThumbnail = true, $returnResizedDimension = true )
	{
		if ( $useThumbnail && !empty( $this->ar->media_thumbFileName ) )
		{
			$this->fileName = $this->ar->media_thumbFileName;
			$this->_imageInfo = null;
		}

		$originalSizes = $this->getOriginalSizes();
		if ( $width == $originalSizes['width'] && $height == $originalSizes['height'] )
		{
			return array('imageType' => IMG_JPG, 'fileName' => $this->getFileName(), 'width' => $width, 'height' => $height, 'originalWidth'=> $width, 'originalHeight'=>  $height );
		}

		$cacheFileName = $this->_verifyCache( $width, $height, $crop, $cropOffset, $forceSize);
		if ($cacheFileName!==false)
		{
			if ( $returnResizedDimension )
			{
				list($width, $height) = getImageSize($cacheFileName);
			}
			else
			{
				$width = NULL;
				$height = NULL;
			}
			return array('imageType' => IMG_JPG, 'fileName' => $cacheFileName, 'width' => $width, 'height' => $height, 'originalWidth'=> NULL, 'originalHeight'=>  NULL);
		}

		if ( $returnResizedDimension )
		{
			$sizes = $this->resizeImageGetFinalSizes($width, $height, $crop, $cropOffset, $forceSize);
			$newWidth = $crop ? min($sizes['width'], $width) : $sizes['width'];
			$newHeight = $crop ? min($sizes['height'], $height) : $sizes['height'];
		}
		else
		{
			$newWidth = NULL;
			$newHeight = NULL;
		}


		return array(	'imageType' 		=> NULL,
						'fileName' 			=> org_glizycms_helpers_Media::getImageUrlById($this->id, $width, $height, $crop, $cropOffset, $forceSize, $useThumbnail ),
						'width' 			=> $newWidth,
						'height' 			=> $newHeight,
						'originalWidth' 	=> NULL,
						'originalHeight' 	=> NULL);
	}

	function resizeImage($cacheFileName, $width, $height, $crop=false, $cropOffset=1, $forceSize=false, $usePiramidalSizes = true, $resize = true)
	{
		$objImg = NULL;
		// cerca l'immagine piramidale da cui partire
		$originalFileName = $this->fileName;
		$originalImageInfo = $this->_imageInfo;

		if ( $usePiramidalSizes && $resize )
		{
			 $finalSizes = $this->resizeImageGetFinalSizes($width, $height, $crop, $cropOffset, $forceSize);
			 $finalWidth = $finalSizes['width'];
			 $finalHeight = $finalSizes['height'];
			 $useSize = null;
			 foreach ( $this->_piramidImageSizes as $v )
			 {
				if ( $v[ 0 ] > $finalWidth && $v[ 1 ] > $finalHeight )
				{
					$useSize = $v;
				}
			}

			if ( !is_null( $useSize ) ) {
				$oldWatermark = $this->watermark;
				$this->watermark = false;
				$resizedPiramidImage =  $this->getResizeImage( $useSize[ 0 ], $useSize[ 1 ], false, 1, false, true, false);
				$this->fileName = $resizedPiramidImage[ 'fileName' ];
				$this->_imageInfo = null;
				$this->watermark = $oldWatermark;
			}
		}
		if (__Config::get('glizy.media.imageMagick')==true) {
			$result = $this->resizeImage_im($cacheFileName, $width, $height, $crop, $cropOffset, $forceSize, $usePiramidalSizes, $resize);
		} else {
			$result = $this->resizeImage_gd($cacheFileName, $width, $height, $crop, $cropOffset, $forceSize, $usePiramidalSizes, $resize);
		}

        $this->fileName = $originalFileName;
        $this->_imageInfo = $originalImageInfo;
        return $result;
	}

	private function insertWatermark(&$img, $width, $height, $mode) {
		$minWidth = __Config::get('IMG_WATERMARK_MIN_WIDTH');
		$minHeight = __Config::get('IMG_WATERMARK_MIN_HEIGHT');
		$watermarkFile = __Config::get('IMG_WATERMARK');
		$pos = __Config::get('IMG_WATERMARK_POS') ?: 'RB';
		$watermarkFile = $watermarkFile ? __Paths::getRealPath('BASE', $watermarkFile) : false;
		if(!$watermarkFile || ($minWidth >= $width && $minHeight >= $height)) {
			return;
		}
		$perc = __Config::get('IMG_WATERMARK_SIZE_PERC');
		if($mode == 'Imagick') {
			$watermark = new Imagick();
            $watermark->readImage($watermarkFile);
            $w_height = $watermark->getImageHeight();
            $w_width = $watermark->getImageWidth();
		} else {
			$watermark = imagecreatefrompng($watermarkFile);
			imagealphablending($watermark, true);
			$w_width = imagesx($watermark);
			$w_height = imagesy($watermark);
		};
		if($width > $height) {
			$sy = $height*$perc/100;
			$sx = $w_width/$w_height*$sy;
		} else {
			$sx = $width*$perc/100;
			$sy = $sx*$w_height/$w_width;
		}
        $marg_left = $width/100;
        //$margin_right = $margin_left;
        $marg_top = $height/100;
        //$marg_bottom = $margin_top;
        switch($pos) {
			case "LT":
				$posx = $marg_left;
				$posy = $marg_top;
				break;
			case "LC":
				$posx = $marg_left;
				$posy = $height/2 - $w_height/2;
				break;
			case "LB":
				$posx = $marg_left;
				$posy = $height - $sy - $marg_top;
				break;
			case "CT":
				$posx = $width/2 - $w_width/2;
				$posy = $marg_top;
				break;
			case "CC":
				$posx = $width/2 - $w_width/2;
				$posy = $height/2 - $w_height/2;
				break;
			case "CB":
				$posx = $width/2 - $w_width/2;
				$posy = $height - $sy - $marg_top;
				break;
			case "RT":
				$posx = $width -$sx -$marg_left;
				$posy = $marg_top;
				break;
			case "RC":
				$posx = $width -$sx -$marg_left;
				$posy = $height/2 - $w_height/2;
				break;
			case "RB":
			default:
				$posx = $width -$sx -$marg_left;
				$posy = $height - $sy - $marg_top;
		}

		if($mode == 'Imagick') {
			 $watermark->scaleImage($sx, $sy);
			 $img->compositeImage($watermark, imagick::COMPOSITE_OVER, $posx, $posy);
			 $watermark->clear();
			 $watermark->destroy();
		} else {
			imagecopyresampled($img, $watermark, $posx, $posy, 0, 0, $sx, $sy, imagesx($watermark), imagesy($watermark));
			imagedestroy($watermark);
		}
	}

	private function resizeImage_gd($cacheFileName, $width, $height, $crop=false, $cropOffset=1, $forceSize=false, $usePiramidalSizes = true, $resize = true)
	{
        $objImg = null;
		if ( file_exists( $this->getFileName() ) )
		{
			$imagetype = $this->getOriginalImageType();

			switch ($imagetype) {
				case  'image/gif':
					if (function_exists('imageCreateFromGIF'))
					{
						$objImg = imageCreateFromGIF($this->getFileName()) ;
					}
					else
					{
						$objImg = imageCreateFromPNG($this->getFileName()) ;
					}
					break;
				case 'image/jpeg':
					$objImg = imageCreateFromJPEG($this->getFileName()) ;
					break;
				case 'image/png':
				   $objImg = imageCreateFromPNG($this->getFileName()) ;
					break;
				case 'image/vnd.wap.wbmp':
				   $objImg = imageCreateFromWBMP($this->getFileName());
					break;
				default:
					//	$ermsg = $imageInfo['mime'].' images are not supported<br />';
					break;
			}
		}

		$originalSizes = $this->getOriginalSizes();

		if (!is_null($objImg))
		{
			$finalSizes = $this->resizeImageGetFinalSizes($width, $height, $crop, $cropOffset, $forceSize);
			$img = imageCreateTrueColor($crop ? $width : $finalSizes['width'], $crop ? $height : $finalSizes['height']);
			$w = imagecolorallocate($img, 255, 255, 255);
			imagefill($img, 0, 0, $w);
			if($resize) {
				imageCopyResampled($img, $objImg, $finalSizes['cropX'], $finalSizes['cropY'], 0, 0, $finalSizes['width'], $finalSizes['height'], $originalSizes['width'], $originalSizes['height']);
				$unsharpMask = __Config::get('glizy.media.image.unsharpMask');
				if ($unsharpMask) {
					list($a, $r, $t) = explode(',', $unsharpMask);
					glz_importLib('phpUnsharpMask/phpUnsharpMask.php');
					phpUnsharpMask::applyUnsharpMask($img, $a, $r, $t);
				}
			} else {
				imageCopy($img, $objImg, 0, 0, 0, 0, $originalSizes['width'], $originalSizes['height']);
			}

			if($this->watermark) {
				$this->insertWatermark($img, $crop ? $width : $finalSizes['width'], $crop ? $height : $finalSizes['height'], 'gd', false);
			}

			imageJPEG($img, $cacheFileName, org_glizy_Config::get('JPG_COMPRESSION') );
			imageDestroy($objImg);
			imageDestroy($img);
			unset( $objImg );
			unset( $img );
			@touch($cacheFileName, filemtime($this->getFileName()));
			@chmod( $cacheFileName, 0777 );
			$retInfo = array(	'imageType' 		=> IMG_JPG,
							'fileName' 			=> $cacheFileName,
							'width' 			=> $crop ? $width : $finalSizes['width'],
							'height' 			=> $crop ? $height : $finalSizes['height'],
							'originalWidth'		=> $originalSizes['width'],
							'originalHeight'	=>  $originalSizes['height']);
		}
		else
		{
			$fileName = org_glizy_Assets::get('ICON_MEDIA_IMAGE');
			list($width, $height, $imagetypes) = getImageSize($fileName);
			$retInfo = array(	'imageType' 		=> IMG_GIF,
							'fileName' 			=> $fileName,
							'width' 			=> $width,
							'height' 			=> $height,
							'originalWidth' 	=> $width,
							'originalHeight' 	=> $height);
		}
		return $retInfo;
	}


	private function resizeImage_im($cacheFileName, $width, $height, $crop=false, $cropOffset=1, $forceSize=false, $usePiramidalSizes = true, $resize = true)
	{
		if ( file_exists( $this->getFileName() ) )
		{
			$originalSizes = $this->getOriginalSizes();
			$finalSizes = $this->resizeImageGetFinalSizes($width, $height, $crop, $cropOffset, $forceSize);

            $filename = utf8_encode(realpath($this->getFileName()));
    		$cacheFileName = str_replace(__Paths::get('CACHE'), org_glizy_Paths::getRealPath('CACHE'), $cacheFileName);
            $thumb = new Imagick();
            $thumb->readImage($this->getFileName());
            if($resize) {
            	$thumb->resizeImage($finalSizes['width'], $finalSizes['height'], Imagick::FILTER_LANCZOS, 1);
	            if ($crop) {
	                $thumb->cropImage($width, $height, -$finalSizes['cropX'], -$finalSizes['cropY']);
	            }
                $thumb->setImageCompressionQuality(org_glizy_Config::get('JPG_COMPRESSION'));
	            $thumb->stripImage();
            }

            if($this->watermark) {
				$this->insertWatermark($thumb, $crop ? $width : $finalSizes['width'], $crop ? $height : $finalSizes['height'], 'Imagick', false);
			}

            $thumb->writeImage($cacheFileName);

            $thumb->clear();
			$thumb->destroy();
			@touch($cacheFileName, filemtime($this->getFileName()));
			@chmod( $cacheFileName, 0777 );
			$retInfo = array(	'imageType' 		=> IMG_JPG,
							'fileName' 			=> $cacheFileName,
							'width' 			=> $crop ? $width : $finalSizes['width'],
							'height' 			=> $crop ? $height : $finalSizes['height'],
							'originalWidth'		=> $originalSizes['width'],
							'originalHeight'	=>  $originalSizes['height']);
		}
		else
		{
			$fileName = org_glizy_Assets::get('ICON_MEDIA_IMAGE');
			list($width, $height, $imagetypes) = getImageSize($fileName);
			$retInfo = array(	'imageType' 		=> IMG_GIF,
							'fileName' 			=> $fileName,
							'width' 			=> $width,
							'height' 			=> $height,
							'originalWidth' 	=> $width,
							'originalHeight' 	=> $height);
		}

		return $retInfo;
	}

	function resizeImageGetFinalSizes($width, $height, $crop=false, $cropOffset=1, $forceSize=false)
	{
		$sizes 			= $this->getOriginalSizes();
		$originalWidth 	= $sizes['width'];
		$originalHeight = $sizes['height'];
		$cropX 			= 0;
		$cropY 			= 0;
        // $destHeight = $height;
        // $destWidth 	= $height/$originalHeight*$originalWidth;

		if ( is_null( $originalWidth  ) ) return;

		if ( ( $width == '*' || !$width) && ($height == '*' || !$height) )
		{
			$width = $originalWidth;
			$height = $originalHeight;
		}
		else if ( $width == '*' || is_null( $width ) || $width == 0 )
		{
			$height = intval($height);
			$width = round($originalWidth * $height / $originalHeight);
		}
		else if( $height == '*' || is_null( $height ) || $height == 0 )
		{
			$width = intval($width);
			$height = round($originalHeight * $width / $originalWidth);
		}
		else
		{
			$width = intval($width);
			$height = intval($height);
			if (!$crop)
			{
				if (!$forceSize)
				{
					if (($originalWidth/$originalHeight) > ($width/$height))
					{
					   $height = round($originalHeight * $width / $originalWidth);
					}
					else
					{
					   $width = round($originalWidth * $height / $originalHeight);
					}
				}
			}
			else
			{
				if (($originalWidth/$originalHeight) > ($width/$height))
				{
					$destHeight = $height;
					$destWidth 	= $height/$originalHeight*$originalWidth;
					switch ($cropOffset)
					{
						case 0:
							break;
						case 1:
							$cropX = round(($width-$destWidth)/2);
							break;
						case 2:
							$cropX = round($width-$destWidth);
							break;
					}
				}
				else
				{
					$destWidth 	= $width;
					$destHeight = $width/$originalWidth*$originalHeight;
					switch ($cropOffset)
					{
						case 0:
							break;
						case 1:
							$cropY = round(($height-$destHeight)/2);
							break;
						case 2:
							$cropY = round($height-$destHeight);
							break;
					}
				}
			}
		}

		if (!isset($destWidth))
		{

			$destWidth 	= $width;
			$destHeight = $height;
		}
		if (!$this->allowDownload) {
			$maxWidth = intval(__Config::get('IMG_DOWNLOAD_WIDTH'));
		    $maxHeight = intval(__Config::get('IMG_DOWNLOAD_HEIGHT'));
		    if ($maxWidth && $maxHeight && ($maxWidth < $destWidth || $maxHeight < $destHeight)) {
			    $destWidth = min($maxWidth, $destWidth);
			    $destHeight = min($maxHeight, $destHeight);
			    if (!$crop) {
			    	$destWidth = $originalWidth/$originalHeight > 1 ? '*' : $destWidth;
			    	$destHeight = $originalWidth/$originalHeight > 1 ? $destHeight : '*';
			    }
			    return $this->resizeImageGetFinalSizes($destWidth, $destHeight, $crop, $cropOffset, $forceSize);
		    }
		}

		return array('width' => intval($destWidth), 'height' => intval($destHeight), 'cropX' => $cropX, 'cropY' => $cropY);
	}

	function _verifyCache( $width, $height, $crop, $cropOffset, $forceSize)
	{
		$cacheOptions = array(
			'cacheDir' => org_glizy_Paths::get('CACHE_IMAGES'),
			'lifeTime' => org_glizy_Config::get('CACHE_IMAGES'),
			'readControlType' => '',
			'fileExtension' => '.jpg'
		);

		$this->_cacheObj = &org_glizy_ObjectFactory::createObject('org.glizy.cache.CacheFile', $cacheOptions);
		$fileName = $this->getFileName().$width.'_'.$height.'_'.($crop ? '1' : '0').'_'.$cropOffset.'_'.($forceSize ? '1' : '0').'_'.($this->watermark ? '1' : '0');
		return $this->_cacheObj->verify($fileName, get_class($this), false, $this->getFileName());
	}
}