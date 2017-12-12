<?php

use Hashids\Hashids;

/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizycms_mediaArchive_BridgeNew extends org_glizycms_mediaArchive_Bridge
{
    public function mediaByIdUrl($id)
    {
        $media = org_glizycms_mediaArchive_MediaManager::getMediaById($id);
        if ($media) {
            $hashGenerator = __ObjectFactory::createObject('org.glizy.helpers.HashGenerator');
            $hash = $hashGenerator->encode($id);
            return __Link::makeURL('bridge-download-media', array('hash' => $hash, 'filename' => $media->originalFileName));
        }
    }
}