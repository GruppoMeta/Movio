<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */


class org_glizycms_mediaArchive_media_Media extends GlizyObject
{
    var $id;
    var $fileName;
    var $size;
    var $type;
    var $title;
    var $description;
    var $creationDate;
    var $modificationDate;
    var $category;
    var $author;
    var $date;
    var $originalFileName;
    var $copyright;
    var $allowDownload;
    var $watermark;
    var $ar;

    private $remoteCacheLifetime;

    function __construct(&$ar)
    {
        if ( is_object( $ar ) )
        {
            $this->ar               = $ar;
            $this->id               = $ar->media_id;
            $this->fileName         = $ar->media_fileName;
            $this->size             = $ar->media_size;
            $this->type             = $ar->media_type;
            $this->title            = glz_encodeOutput($ar->media_title);
            $this->description      = $ar->media_description;
            $this->creationDate     = $ar->media_creationDate;
            $this->modificationDate = $ar->media_modificationDate;
            $this->category         = $ar->media_category;
            $this->author           = $ar->media_author;
            $this->date             = $ar->media_date;
            $this->zoom             = $ar->media_zoom;
            $this->originalFileName = $ar->media_originalFileName ? $ar->media_originalFileName : $ar->media_fileName;
            $this->copyright        = $ar->media_copyright;
            $this->allowDownload   = $ar->media_allowDownload;
            $this->watermark        = $ar->media_watermark;
        }
        else
        {
            $this->id               = $ar['media_id'];
            $this->fileName         = $ar['media_fileName'];
            $this->size             = $ar['media_size'];
            $this->type             = $ar['media_type'];
            $this->title            = glz_encodeOutput($ar['media_title']);
            $this->creationDate     = $ar['media_creationDate'];
            $this->modificationDate = $ar['media_modificationDate'];
            $this->category         = $ar['media_category'];
            $this->author           = $ar['media_author'];
            $this->date             = $ar['media_date'];
            $this->zoom             = $ar['media_zoom'];
            $this->originalFileName = !empty($ar['media_originalFileName']) ? $ar['media_originalFileName'] : $ar['media_fileName'];
            $this->copyright        = $ar['media_copyright'];
            $this->allowDownload   = $ar['media_allowDownload'];
            $this->watermark        = $ar['media_watermark'];
        }

        $this->remoteCacheLifetime = __Config::get('glizy.media.image.remoteCache.lifetime');
    }

    function isMapped()
    {
        return preg_match('/([^:]+):\/\/(.+)/', $this->fileName);
    }

    function getFileName( $checkIfExists=true )
    {
        $file = $this->resolveFileName();
        if ( !$checkIfExists ) {
            return $file;
        } else {
            return file_exists($file) ? $file : org_glizy_Assets::get('ICON_MEDIA_IMAGE');
        }
    }

    function exists()
    {
        $file = $this->resolveFileName();
        return file_exists($file);
    }

    function getIconFileName()
    {
        return org_glizy_Assets::get('ICON_MEDIA_IMAGE');
    }

    function getResizeImage($width, $height, $crop=false, $cropOffset=1, $forceSize=false, $returnResizedDimension=true, $usePiramidalSizes = true)
    {
        return array('imageType' => IMG_JPG, 'fileName' => $this->getIconFileName(), 'width' => NULL, 'height' => NULL, 'originalWidth'=> NULL, 'originalHeight'=>  NULL);
    }

    function getThumbnail( $width, $height, $crop=false, $cropOffset = 0 )
    {
        $iconPath = $this->getIconFileName();
        // controlla se c'è un'anteprima associata
        if ( !empty( $this->ar->media_thumbFileName ) )
        {
            // TODO: da implementare meglio in modo che i metodi di resize
            // non siano in Image ma comuni a tutti i media
            $this->ar->media_fileName = $this->ar->media_thumbFileName;
            $this->ar->media_type = 'IMAGE';
            $media = org_glizycms_mediaArchive_MediaManager::getMediaByRecord( $this->ar );
            return $media->getThumbnail( $width, $height );
        }
        list( $originalWidth, $originalHeight, $imagetypes ) = getImageSize($iconPath);
        return array('fileName' => $iconPath, 'width' => $originalWidth, 'height' => $originalHeight);
    }

    function addDownloadCount()
    {
        $this->ar->media_download++;
        $this->ar->save();
    }

    private function resolveFileName()
    {
        if ($this->isRemoteFile()) {
            $file = $this->remoteMediaCacheFileName();
            return $this->retrieveRemoteMedia($file) ? $file : false;
        }

        // gestione mapping delle cartelle
        if (__Config::get('glizycms.mediaArchive.mediaMappingEnabled') && preg_match('/([^:]+):\/\/(.+)/', $this->fileName, $m)) {
            $application = org_glizy_ObjectValues::get('org.glizy', 'application' );
            if ($application) {
                $mappingService = $application->retrieveProxy('org.glizycms.mediaArchive.services.MediaMappingService');
            } else {
                $mappingService = org_glizy_objectFactory::createObject('org.glizycms.mediaArchive.services.MediaMappingService');
            }
            $targetPath = $mappingService->getMapping($m[1]);
            $this->fileName = $targetPath.'/'.$m[2];
        }

        if ( strpos( $this->fileName, '/' ) === false ) {
            $file = org_glizy_Paths::get('APPLICATION_MEDIA_ARCHIVE').ucfirst(strtolower($this->type)).'/'.$this->fileName;
        } else {
            $file = $this->fileName;
        }

        return $file;
    }

    private function retrieveRemoteMedia($fileName)
    {
        if (file_exists($fileName) && time()-filemtime($fileName) < $this->remoteCacheLifetime) {
            return true;
        }

        $folder = pathinfo($fileName, PATHINFO_DIRNAME);
        if (!file_exists($folder)) {
            @mkdir($folder, 0755, true);
        }

        try {
            $remoteFileHandle = fopen($this->fileName, 'rb');
            $localFileHandle = fopen($fileName, 'wb');
            while ($buffer = fread($remoteFileHandle, 64*1024)) {
                fwrite($localFileHandle, $buffer);
            }

            if (is_resource($remoteFileHandle)) fclose($remoteFileHandle);
            if (is_resource($localFileHandle)) fclose($localFileHandle);

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    private function remoteMediaCacheFileName()
    {
        return org_glizy_Paths::get('CACHE_IMAGES').'/media/'.md5($this->fileName);
    }

    /**
     * @return boolean
     */
    public function isRemoteFile()
    {
       return preg_match('/^(http:|https:)/', $this->fileName);
    }

    /**
     * @return string
     */
    public function getFileNameOrRemoteUrl()
    {
       return $this->isRemoteFile() ? $this->fileName : $this->getFileName();
    }

}
