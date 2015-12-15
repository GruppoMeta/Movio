<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizycms_mediaArchive_Bridge implements org_glizycms_mediaArchive_IBridge
{
    public function getMediaById($id)
    {
        return 'getFile.php?id='.$id;
    }

    public function getImageById($id)
    {
        return 'getImage.php?id='.$id;
    }

    public function getImageByIdAndResize($id, $width, $height, $crop=false, $cropOffset=1, $forceSize=false, $useThumbnail=false)
    {
        return 'getImage.php?id='.$id.'&w='.$width.'&h='.$height.'&c='.($crop ? '1' : '0').'&co='.$cropOffset.'&f='.($forceSize ? '1' : '0').'&t='.($useThumbnail ? '1' : '0').'&.jpg';
    }

    public function getJsonFromAr(org_glizy_dataAccessDoctrine_ActiveRecord $ar)
    {
        return json_encode(array(
                        'id' => $ar->media_id,
                        'filename' => $ar->media_fileName,
                        'title' => $ar->media_title,
                        'src' => $ar->thumb_filename,
                        'category' => $ar->media_category,
                        'author' => $ar->media_author,
                        'date' => $ar->media_date,
                        'copyright' => $ar->media_copyright,
                ));
    }

    public function getMediaPickerUrl($tinyVersion=false, $mediaType='ALL')
    {
        if (!$tinyVersion) {
            return 'index.php?pageId=mediaarchive_picker&mediaType='.$mediaType.'&';
        } else {
            return 'index.php?pageId=MediaArchive_pickerTiny&mediaType='.$mediaType.'&';
        }
    }

    public function getImageResizeTemplate($tinyVersion=false, $mediaType='ALL')
    {
        return $this->getImageByIdAndResize('#id#', '#w#', '#h#', false, 1, true);
    }

    public function getIdFromJson($json)
    {
        if (!is_null($json->id)) {
            return $json->id;
        } else {
            preg_match('/getImage.php\?id=(\d+)/', $json->src, $m);
            return $m[1];
        }
    }
}