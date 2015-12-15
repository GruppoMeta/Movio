<?php
class org_glizycms_mediaArchive_controllers_mediaEdit_ajax_Save extends org_glizy_mvc_core_CommandAjax
{
    protected $mediaIds = array();

    public function execute($data)
    {
        $data = json_decode($data);

        if (__Config::get('glizycms.mediaArchive.mediaMappingEnabled') && $data->mediaFileServer) {
            $result = $this->saveMediasFromServer($data);
            return $this->processResult($result);
        } else if (property_exists($data, 'medias')) {
            $result = $this->saveMedias($data);
            return $this->processResult($result);
        } else if ($data->media_id){
            return $this->modifyMedia($data);
        } else {
            $this->directOutput = true;
            return array('evt' => array('No medias'));
        }
    }

    protected function processResult($result)
    {
        $this->directOutput = true;
        if (is_array($result) && $result['errors']) {
            return $result;
        } else {
            $application = &org_glizy_ObjectValues::get('org.glizy', 'application');
            $url = org_glizy_helpers_Link::makeUrl( $this->getRedirectUrl(), array( 'pageId' => $application->getPageId() ) );
            return array('url' => $url);
        }
    }

    public function saveMediasFromServer($data)
    {
        $file_path = $data->mediaFileServer;
        $file_virtual_path = preg_replace('/\//', '://', $file_path, 1);

        $application = &org_glizy_ObjectValues::get('org.glizy', 'application');
        $mappingService = $application->retrieveProxy('org.glizycms.mediaArchive.services.MediaMappingService');
        $file_path = $mappingService->getRealPath($file_path);
        $file_name = pathinfo($file_path, PATHINFO_BASENAME);

        $media = new StdClass();
        foreach ($data as $k => $v) {
            $media->$k = $v;
        }
        $media->media_fileName = $file_virtual_path;
        $media->__filePath = $file_path;
        $media->__originalFileName = $file_name;

        $mediaProxy = org_glizy_ObjectFactory::createObject('org.glizycms.mediaArchive.models.proxy.MediaProxy');
        $currentMediaId = $mediaProxy->saveMedia($media, $data->copyToCMS == 'true' ? $mediaProxy::COPY_TO_CMS : $mediaProxy::NONE);
        array_push($this->mediaIds,$currentMediaId);
        return $currentMediaId;
    }

    public function saveMedias($data)
    {
        $mediaProxy = org_glizy_ObjectFactory::createObject('org.glizycms.mediaArchive.models.proxy.MediaProxy');
        $medias = $data->medias;

        $r = $this->checkDuplicates($medias);
        if (!$r) {
            return $r;
        }

        $medias = $this->decompressFiles($medias);

        $uploadedFiles = 0;
        for ($i = 0; $i < count($medias->__uploadFilename); $i++) {
            if (!$medias->__uploadFilename[$i]) continue;

            $media = new StdClass();
            foreach ($medias as $k => $v) {
                if ($v[$i]) {
                    $media->$k = $v[$i];
                }
            }
            $media->__filePath = realpath($medias->__uploadFilename[$i]);
            try {
                $result = $mediaProxy->saveMedia($media);
            } catch (Exception $e) {
                var_dump($e->getErrors());
            }

            if (is_array($result) && $result['errors']) {
                return $result;
            }

            array_push($this->mediaIds,$result);
            $uploadedFiles++;
        }

        if (!$uploadedFiles) {
            return array('errors' => array(__T('Nessun file caricato')));
        }

        return true;
    }

    public function modifyMedia($data)
    {
        $media = org_glizy_ObjectFactory::createModel('org.glizycms.models.Media');

        $media->load($data->media_id);

        $media->media_modificationDate = new org_glizy_types_DateTime();

        foreach ($data as $k => $v) {
            // remove the system values
            if (strpos($k, '__') === 0 || !$media->fieldExists($k)) continue;
            $media->$k = $v;
        }

        $media->media_FK_user_id = org_glizy_ObjectValues::get('org.glizy', 'userId');

        try {
            return $media->save();
        }
        catch (org_glizy_validators_ValidationException $e) {
            $this->directOutput = true;
            return array('errors' => $e->getErrors());
        }
    }

    public function getRedirectUrl()
    {
        return 'glizycmsMediaArchiveAdd';
    }

    private function checkDuplicates($medias)
    {
        // controlla se il file esiste già nell'archivio
        $ar = org_glizy_ObjectFactory::createModel('org.glizycms.models.Media');
        if ($ar->getField('media_md5')) {
            for ($i = 0; $i < count($medias->__uploadFilename); $i++) {
                if (!$medias->__uploadFilename[$i]) continue;

                $md5 = md5_file(realpath($medias->__uploadFilename[$i]));

                $ar->emptyRecord();
                $result = $ar->find(array('media_md5' => $md5));

                if ($result) {
                    return array('errors' => array(__T('File already in media archive', $medias->__originalFileName[$i])));
                }
            }
        }
        return true;
    }

    private function decompressFiles($medias)
    {
        if (!property_exists($medias, '__expand')) {
            return $medias;
        }

        GlizyClassLoader::addLib('VIPSoft\Unzip', __Paths::get('APPLICATION_LIBS') . 'VIPSoft/Unzip');

        $copyProperties = function(&$properties, &$medias, &$tempMedias, $pos) {
            foreach($properties as $v) {
                $tempMedias->{$v}[] = @$medias->{$v}[$pos];
            }
        };

        $properties = array_keys(get_object_vars($medias));
        if (!in_array('media_title', $properties)) {
            $properties[] = 'media_title';
        }
        $tempMedias = new StdClass();
        foreach($properties as $v) {
            $tempMedias->$v = array();
        }
        $numUploaded = count($medias->__uploadFilename);
        for ($i = 0; $i<$numUploaded; $i++) {
            if (!$medias->__uploadFilename[$i]) continue;
            if ($medias->__expand[$i]==1) {
                $unzipper  = new VIPSoft\Unzip\Unzip();
                $destFolder = $medias->__uploadFilename[$i].md5(time());
                $filenames = $unzipper->extract($medias->__uploadFilename[$i], $destFolder);
                foreach($filenames as $f) {
                    if (strpos($f, '__MACOSX')!==false || strpos($f, '.DS_Store')!==false) continue;
                    $filename = $destFolder.'/'.$f;
                    if (is_dir($filename)) continue;
                    $pos = count($tempMedias->__uploadFilename);
                    $copyProperties($properties, $medias, $tempMedias, $i);
                    $info = pathinfo($filename);
                    $tempMedias->__uploadFilename[$pos] = $filename;
                    $tempMedias->__originalFileName[$pos] = $info['basename'];
                    $tempMedias->media_title[$pos] = str_replace(array('_', '-'), ' ', $info['filename']);
                }
            } else {
                $copyProperties($properties, $medias, $tempMedias, $i);
            }
        }

        return $tempMedias;
    }
}