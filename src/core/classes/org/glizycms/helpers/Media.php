<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizycms_helpers_Media extends GlizyObject
{

	public static function getImageById($id, $direct=false, $cssClass='', $style='', $onclick='')
	{
		$media = &org_glizycms_mediaArchive_MediaManager::getMediaById($id);
		if (is_null($media)) {
			return '';
		}
		$attributes = array();
		$attributes['alt'] = $media->title;
		$attributes['title'] = $media->title;
		$attributes['class'] = $cssClass;
		$attributes['style'] = $style;
		$attributes['onclick'] = $onclick;
		$attributes['src'] = $direct ? $media->getFileName() : org_glizycms_Glizycms::getMediaArchiveBridge()->imageByIdUrl($id);
		;
		return org_glizy_helpers_Html::renderTag('img', $attributes);
	}


	public static function getResizedImageById($id, $direct=false, $width, $height, $crop=false, $cssClass='', $style='', $onclick='')
	{
		$media = &org_glizycms_mediaArchive_MediaManager::getMediaById($id);
		if (is_null($media)) {
			return '';
		}
		if ($direct) {
			$thumb = $media->getThumbnail($width, $height, $crop);
		}
		$attributes = array();
		$attributes['alt'] = $media->title;
		$attributes['title'] = $media->title;
		$attributes['class'] = $cssClass;
		$attributes['style'] = $style;
		$attributes['onclick'] = $onclick;
		$attributes['src'] = $direct ? $thumb['fileName'] : org_glizycms_Glizycms::getMediaArchiveBridge()->imageByIdAndResizedUrl($id, $width, $height, $crop);
		;
		return org_glizy_helpers_Html::renderTag('img', $attributes);
	}

	public static function getImageUrlById($id, $width, $height, $crop=false, $cropOffset=1, $forceSize=false, $useThumbnail=false )
	{
		return org_glizycms_Glizycms::getMediaArchiveBridge()->imageByIdAndResizedUrl($id, $width, $height, $crop, $cropOffset, $forceSize, $useThumbnail);
	}

	public static function getResizedImageUrlById($id, $direct=false, $width, $height, $crop=false, $cropOffset=1, $forceSize=false )
	{
		if ($direct) {
			$media = &org_glizycms_mediaArchive_MediaManager::getMediaById($id);
			if (is_null($media)) {
				return '';
			}
			$thumb = $media->getThumbnail($width, $height, $crop, $cropOffset, $forceSize );
			return $thumb['fileName'];
		}
		return self::getImageUrlById( $id, $width, $height, $crop, $cropOffset, $forceSize );
	}

	public static function getUrlById($id, $direct=false)
	{
		if ($direct) {
			$media = &org_glizycms_mediaArchive_MediaManager::getMediaById($id);
			return is_null($media) ? '' : $media->getFileName();
		} else {
			return org_glizycms_Glizycms::getMediaArchiveBridge()->imageByIdUrl($id);
		}
	}

	public static function getFileUrlById($id, $direct=false)
	{
		if ($direct) {
			$media = &org_glizycms_mediaArchive_MediaManager::getMediaById($id);
			return is_null($media) ? '' : $media->getFileName(false);
		} else {
			return org_glizycms_Glizycms::getMediaArchiveBridge()->mediaByIdUrl($id);
		}
	}

	/* deprecated */
	public static function getThumbnailById($id, $width, $height, $crop=false, $class='', $onclick='')
	{
		return self::getResizedImageById($id, false, $width, $height, $crop, $class, '', '');
	}
}

class __Media extends org_glizycms_helpers_Media
{
}