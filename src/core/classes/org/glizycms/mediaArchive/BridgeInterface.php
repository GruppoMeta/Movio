<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

interface org_glizycms_mediaArchive_BridgeInterface
{
    public function mediaByIdUrl($id);
    public function imageByIdUrl($id);
    public function imageByIdAndResizedUrl($id, $width, $height, $crop=false, $cropOffset=1, $forceSize=false, $useThumbnail=false);
    public function jsonFromModel($model);
    public function mediaPickerUrl($tinyVersion=false, $mediaType='ALL');
    public function mediaTemplateUrl();
    public function imageTemplateUrl();
    public function imageResizeTemplateUrl($width='#w#', $height='#h#', $crop=false, $cropOffset=1);
    public function mediaIdFromJson($json);
    public function mediaInfo($id);
    public function mediaInfoAll($id);
    public function serveMedia($id);
    public function serveImage($id, $width, $height, $crop=false, $cropOffset=1, $forceSize=false, $useThumbnail=false);
}