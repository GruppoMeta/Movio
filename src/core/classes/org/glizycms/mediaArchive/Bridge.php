<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizycms_mediaArchive_Bridge implements org_glizycms_mediaArchive_BridgeInterface
{
    public function mediaByIdUrl($id)
    {
        return 'getFile.php?id='.$id;
    }

    public function imageByIdUrl($id)
    {
        return 'getImage.php?id='.$id;
    }

    public function imageByIdAndResizedUrl($id, $width, $height, $crop=false, $cropOffset=1, $forceSize=false, $useThumbnail=false)
    {
        return 'getImage.php?id='.$id.'&w='.$width.'&h='.$height.'&c='.($crop ? '1' : '0').'&co='.$cropOffset.'&f='.($forceSize ? '1' : '0').'&t='.($useThumbnail ? '1' : '0').'&.jpg';
    }

    public function jsonFromModel($model)
    {
        return json_encode(array(
                        'id' => $model->media_id,
                        'filename' => $model->media_fileName,
                        'title' => $model->media_title,
                        'src' => $model->thumb_filename,
                        'category' => $model->media_category,
                        'type' => $model->media_type,
                        'author' => $model->media_author,
                        'date' => $model->media_date,
                        'copyright' => $model->media_copyright,
                        'width' => @$model->media_w,
                        'height' => @$model->media_h,
                ));
    }

    public function mediaPickerUrl($tinyVersion=false, $mediaType='ALL')
    {
        if (!$tinyVersion) {
            return GLZ_HOST.'/index.php?pageId=mediaarchive_picker&mediaType='.$mediaType.'&';
        } else {
            return GLZ_HOST.'/index.php?pageId=MediaArchive_pickerTiny&mediaType='.$mediaType.'&';
        }
    }

    public function mediaTemplateUrl()
    {
        return $this->mediaByIdUrl('#id#');
    }

    public function imageTemplateUrl()
    {
        return $this->mediaByIdUrl('#id#');
    }

    public function imageResizeTemplateUrl($width='#w#', $height='#h#', $crop=false, $cropOffset=1)
    {
        return $this->imageByIdAndResizedUrl('#id#', $width, $height, $crop, $cropOffset);
    }


    public function mediaIdFromJson($json)
    {
        if (!is_null($json->id)) {
            return $json->id;
        } else {
            preg_match('/getImage.php\?id=(\d+)/', $json->src, $m);
            return $m[1];
        }
    }

    public function mediaInfo($id)
    {
        $ar = org_glizy_ObjectFactory::createModel('org.glizycms.models.Media');
        if ($ar->load($id)) {
            return __ObjectFactory::createObject('org.glizycms.mediaArchive.models.vo.MediaInfoVO', $ar);
        }

        return null;
    }


    public function serveMedia($id)
    {
        if (!$id || !$media = org_glizycms_mediaArchive_MediaManager::getMediaById($id)) {
            org_glizy_helpers_Navigation::notFound();
        }

        if ($media->allowDownload) {
            $media->addDownloadCount();
            org_glizy_helpers_FileServe::serve($media->getFileName(), $media->originalFileName);
        } else {
            org_glizy_helpers_Navigation::accessDenied();
        }
    }

    public function serveImage($id, $width, $height, $crop=false, $cropOffset=1, $forceSize=false, $useThumbnail=false)
    {
        if (!$id || !$media = org_glizycms_mediaArchive_MediaManager::getMediaById($id)) {
            org_glizy_helpers_Navigation::notFound();
        }

        if ($useThumbnail && $media->ar->media_thumbFileName) {
            $media->ar->media_fileName = $media->ar->media_thumbFileName;
            $media->ar->media_type = 'IMAGE';
            $media = org_glizycms_mediaArchive_MediaManager::getMediaByRecord( $media->ar );
        }

        if ($media->type=='IMAGE') {
            if ($width && $height) {
                $mediaInfo = $media->getResizeImage($width, $height, $crop, $cropOffset, $forceSize);
            } else if ($media->watermark){
                $originalSize = $media->getOriginalSizes();
                $mediaInfo = $media->getResizeImage($originalSize['width'], $originalSize['height']);
            } else {
                $mediaInfo = $media->getImageInfo();
            }
        } else {
            $mediaInfo = array('fileName' => $media->getIconFileName());
        }

        org_glizy_helpers_FileServe::serve($mediaInfo['fileName'], null, 60 * 60 * 24 * 3);
    }
}