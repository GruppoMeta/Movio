<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizycms_mediaArchive_MediaManager extends GlizyObject
{
    /**
     * @param $id
     *
     * @return GlizyObject|null
     */
    static function &getMediaById($id)
	{
		$ar = org_glizy_ObjectFactory::createModel('org.glizycms.models.Media');
		if ($ar->load($id))
		{
			$media = &org_glizycms_mediaArchive_MediaManager::getMediaByRecord($ar);
			return $media;
		}
		else
		{
			// TODO
			// ERRORE
		}

		return NULL;
	}


	/**
     * @param $ar
     *
     * @return GlizyObject
     */
    static function &getMediaByRecord(&$ar)
	{
		$mediaType = $ar->media_type;
		$mediaTypeInfo = org_glizycms_mediaArchive_MediaManager::getMediaTypeInfo();
		$mediaClassName = 'org.glizycms.mediaArchive.media.'.$mediaTypeInfo[$mediaType]['class'];
		$media = &org_glizy_ObjectFactory::createObject($mediaClassName, $ar);
		return $media;
	}

    /**
     * @param $values
     *
     * @return GlizyObject
     */
	static function &getMediaByValues( $values )
	{
		$mediaType = $values['media_type'];
		$mediaTypeInfo = org_glizycms_mediaArchive_MediaManager::getMediaTypeInfo();
		$mediaClassName = 'org.glizycms.mediaArchive.media.'.$mediaTypeInfo[$mediaType]['class'];
		$media = &org_glizy_ObjectFactory::createObject($mediaClassName, $values );
		return $media;
	}

	/**
     * @param $mediaType
     *
     * @return GlizyObject
     */
    static function &getEmptyMediaByType($mediaType)
	{
		$mediaTypeInfo = org_glizycms_mediaArchive_MediaManager::getMediaTypeInfo();
		$mediaClassName = 'org.glizycms.mediaArchive.media.'.$mediaTypeInfo[$mediaType]['class'];
		$ar = null;
        /** @var org_glizycms_mediaArchive_media_Media $media */
		$media = &org_glizy_ObjectFactory::createObject($mediaClassName, null);
		$media->type = $mediaType;
		return $media;
	}

    /**
     * @return array
     */
	function getMediaTypeInfo()
	{
		$fileTypes = array(	'IMAGE' => 		array('extension' => array('jpg', 'jpeg', 'png', 'gif', 'tif', 'tiff'), 'class' => 'Image'),
							'OFFICE' => 	array('extension' => array('doc', 'xls', 'mdb', 'ppt', 'pps', 'html', 'htm', 'odb', 'odc', 'odf', 'odg', 'odi', 'odm', 'odp', 'ods', 'odt', 'otc', 'otf', 'otg', 'oth', 'oti', 'otp', 'ots', 'ott', 'docx', 'dotx', 'xlsx', 'xltx', 'pptx', 'potx'), 'class' => 'Office'),
							'ARCHIVE' => 	array('extension' => array('zip', 'rar', '7z', 'tar', 'gz', 'tgz'), 'class' => 'Archive'),
							'AUDIO' => 		array('extension' => array('wav', 'mp3', 'aif'), 'class' => 'Audio'),
							'PDF' => 		array('extension' => array('pdf'), 'class' => 'Pdf'),
							'VIDEO' => 		array('extension' => array('avi', 'mov', 'flv', 'wmv', 'mp4', 'm4v', 'mpg'), 'class' => 'Video'),
							'FLASH' => 		array('extension' => array('swf'), 'class' => 'Flash'),
							'OTHER' => 		array('extension' => array(), 'class' => 'Other'),
						);

		$customType = org_glizycms_mediaArchive_MediaManager::getCustomMediaType();
		if (count($customType))
		{
			$fileTypes = array_merge($fileTypes, $customType);
		}

		return $fileTypes;
	}

    /**
     * @param $ext
     *
     * @return int|string
     */
	static function getMediaTypeFromExtension($ext)
	{
		$ext = strtolower($ext);
		$fileTypes = org_glizycms_mediaArchive_MediaManager::getMediaTypeInfo();

		$fileType = 'OTHER';
		foreach($fileTypes as $k=>$v)
		{
			if (in_array($ext, $v['extension']))
			{
				$fileType = $k;
				break;
			}
		}
		return $fileType;
	}

    /**
     * @param $type
     * @param $values
     */
	static function addCustomMediaType($type, $values)
	{
		$customType = &org_glizy_ObjectValues::get('glizy.MediaManager', 'customType', array());
		$customType[$type] = $values;
	}

    /**
     * @return string
     */
	function getCustomMediaType()
	{
		$customType = &org_glizy_ObjectValues::get('glizy.MediaManager', 'customType', array());
		return $customType;
	}
}
