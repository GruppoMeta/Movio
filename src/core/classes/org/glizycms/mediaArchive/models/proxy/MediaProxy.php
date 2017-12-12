<?php

class org_glizycms_mediaArchive_models_proxy_MediaProxy extends GlizyObject
{
    const NONE = 0;
    const MOVE_TO_CMS = 1;
    const COPY_TO_CMS = 2;

    public function deleteMedia($id)
    {
     	$media = org_glizycms_mediaArchive_MediaManager::getMediaById($id);

        if (!$media->isMapped()) {
            $fileName = $media->getFileName();

		    if (file_exists($fileName) && $media->getIconFileName() != $fileName) {
		    	unlink($fileName);
		    }
        }

        $media->ar->delete($id);

        if (__Config::get('glizycms.mediaArchive.exifEnabled')) {
            $exifService = org_glizy_ObjectFactory::createObject('org.glizycms.mediaArchive.services.ExifService');
            $exifService->delete($id);
        }

    }

    public function getMediaJson($mediaMappingName)
    {
        $ar = org_glizy_ObjectFactory::createModel('org.glizycms.models.Media');
    	$result = $ar->find(array('media_fileName' => $mediaMappingName));
        if ($result) {
            return org_glizycms_Glizycms::getMediaArchiveBridge()->jsonFromModel($ar);
        } else {
            return null;
        }
    }

    protected function createMediaRecord($data)
    {
        $filePath = $data->__filePath;
        $originalFileName = $data->__originalFileName;
        $fileExtension = strtolower(pathinfo($data->__originalFileName, PATHINFO_EXTENSION));
        $fileType = org_glizycms_mediaArchive_MediaManager::getMediaTypeFromExtension($fileExtension);
        $media = org_glizy_ObjectFactory::createModel('org.glizycms.models.Media');
        $media->media_fileName = $originalFileName;
        $media->media_originalFileName = $originalFileName;
        $media->media_size = 0;
        $media->media_type = $fileType;
        $media->media_FK_user_id = org_glizy_ObjectValues::get('org.glizy', 'userId');
        $media->media_creationDate = new org_glizy_types_DateTime();
        $media->media_modificationDate = new org_glizy_types_DateTime();
        $media->media_download = 0;
        if($fileExtension == 'tif' || $fileExtension =='tiff') {
            $media->media_watermark = 1;
            $media->media_allowDownload = 0;
        } else {
            $media->media_watermark = 0;
            $media->media_allowDownload = 1;
        }

        foreach ($data as $k => $v) {
            // remove the system values
            if (strpos($k, '__') === 0 || !$media->fieldExists($k)) continue;
            $media->$k = $v;
        }

        $mediaId = $media->save();

        return $mediaId;
    }

    protected function copyFileInArchive($action, $filePath, $originalFileName, $fileType)
    {
        $file_destname = md5(time()) . "_" . $originalFileName;
        $destinationFolder = org_glizy_Paths::get('APPLICATION_MEDIA_ARCHIVE').ucfirst(strtolower($fileType));
        $fileDestinationPath = $destinationFolder.'/'.$file_destname;

        // verifica che la cartella di destinazione sia scrivibile
        if (!is_writeable($destinationFolder)) {
            return array('status' => false, 'errors' => array('Cartella Archivio Media non scrivibile'));
        }

        if ($action == self::MOVE_TO_CMS) {
            rename($filePath, $fileDestinationPath);
        } else if ($action == self::COPY_TO_CMS) {
            copy($filePath, $fileDestinationPath);
        }

        return array('status' => true, 'destName' => $file_destname, 'destPath' => $fileDestinationPath);
    }

    public function saveMedia($data, $action = self::MOVE_TO_CMS, $createRecordIfFileNotExists = false)
    {
        $filePath = $data->__filePath;
        $filePathThumb = property_exists($data, '__filePathThumb') ? $data->__filePathThumb : '';
        // controlla che il file esista
        if (!file_exists($filePath)) {
            if ($createRecordIfFileNotExists) {
                return $this->createMediaRecord($data);
            } else {
                return array('errors' => array('Il file '.pathinfo($filePath, PATHINFO_FILENAME).' non esiste'));
            }
        }

        $originalFileName = $data->__originalFileName;

        $fileSize = filesize($filePath);
        $fileExtension = strtolower(pathinfo($data->__originalFileName, PATHINFO_EXTENSION));
        $fileType = org_glizycms_mediaArchive_MediaManager::getMediaTypeFromExtension($fileExtension);
        $saveExifData = __Config::get('glizycms.mediaArchive.exifEnabled') && $fileType == 'IMAGE';
        if ($saveExifData) {
            $exif = @exif_read_data($filePath);
        }


        if ($action != self::NONE) {
            $r = $this->copyFileInArchive($action, $filePath, $originalFileName, $fileType);
            if (!$r['status']) {
                return $r;
            }

            $data->media_fileName = $r['destName'];
            $fileDestinationPath = $r['destPath'];

            if ($filePathThumb) {
                $r = $this->copyFileInArchive($action, $filePathThumb, 'thumb_'.$originalFileName, $fileType);
                if (!$r['status']) {
                    return $r;
                }
                $filePathThumb = $r['destName'];
            }
            /*
            $file_destname = md5(time()) . "_" . $originalFileName;
            $destinationFolder = org_glizy_Paths::get('APPLICATION_MEDIA_ARCHIVE').ucfirst(strtolower($fileType));
            $fileDestinationPath = $destinationFolder.'/'.$file_destname;
            $data->media_fileName = $file_destname;

            // verifica che la cartella di destinazione sia scrivibile
            if (!is_writeable($destinationFolder)) {
                return array('errors' => array('Rendere scrivibile la cartella '.$destinationFolder));
            }

            if ($action == self::MOVE_TO_CMS) {
                rename($filePath, $fileDestinationPath);
            } else if ($action == self::COPY_TO_CMS) {
                copy($filePath, $fileDestinationPath);
            }

             */
        } else {
            $fileDestinationPath = $filePath;
        }

        $media = org_glizy_ObjectFactory::createModel('org.glizycms.models.Media');
        $media->media_originalFileName = $originalFileName;
        $media->media_thumbFileName = $filePathThumb;
        $media->media_size = $fileSize;
        $media->media_type = $fileType;
        $media->media_FK_user_id = org_glizy_ObjectValues::get('org.glizy', 'userId');
        $media->media_creationDate = new org_glizy_types_DateTime();
        $media->media_modificationDate = new org_glizy_types_DateTime();
        $media->media_download = 0;
        $media->media_md5 = md5_file($fileDestinationPath);
        if($fileExtension == 'tif' || $fileExtension =='tiff') {
            $media->media_watermark = 1;
            $media->media_allowDownload = 0;
        } else {
            $media->media_allowDownload = 1;
            $media->media_watermark = 0;
        }

        foreach ($data as $k => $v) {
            // remove the system values
            if (strpos($k, '__') === 0 || !$media->fieldExists($k)) continue;
            $media->$k = $v;
        }

        if ($saveExifData) {
            if ($exif['COMPUTED']['Copyright'] && empty($media->media_copyright)) {
                $media->media_copyright = $exif['COMPUTED']['Copyright'];
            }
        }

        $mediaId = $media->save();

        if ($saveExifData) {
            $exifService = org_glizy_ObjectFactory::createObject('org.glizycms.mediaArchive.services.ExifService');
            $exifService->saveExifData($mediaId, $exif);
        }

        return $mediaId;
    }
}