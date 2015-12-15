<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

interface org_glizycms_mediaArchive_IBridge
{
    public function getMediaById($id);
    public function getImageById($id);
    public function getImageByIdAndResize($id, $width, $height, $crop=false, $cropOffset=1, $forceSize=false, $useThumbnail=false);
    public function getJsonFromAr(org_glizy_dataAccessDoctrine_ActiveRecord $ar);
    public function getMediaPickerUrl($tinyVersion=false, $mediaType='ALL');
    public function getImageResizeTemplate($tinyVersion=false, $mediaType='ALL');
}