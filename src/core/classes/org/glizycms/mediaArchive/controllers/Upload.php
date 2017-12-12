<?php
use Hashids\Hashids;

class org_glizycms_mediaArchive_controllers_Upload extends org_glizy_mvc_core_Command
{
    public function execute()
    {
        $this->checkPermissionForBackend();
        
        $uploadFolder = __Paths::get('UPLOAD');
        $this->checkPermissionForBackend();

        try {
            $this->checkUploadFolder($uploadFolder);
            $this->deleteOldFIlesInUploadFolder($uploadFolder);
            $this->sendResponse($this->processUploadFile($uploadFolder), false);
        } catch (Exception $e) {
            $this->sendResponse('error', true);
        }
    }

    /**
     * @param  string $uploadFolder
     * @return void
     */
    private function checkUploadFolder($uploadFolder)
    {
        if (!file_exists($uploadFolder)) {
            @mkdir($uploadFolder);
        }
    }

    /**
     * @param  string $uploadFolder
     * @return void
     */
    private function deleteOldFIlesInUploadFolder($uploadFolder)
    {
        org_glizy_helpers_Files::deleteDirectory($uploadFolder, 2*24*60, true);
    }

    /**
     * @param  string $uploadFolder
     * @return array
     */
    private function processUploadFile($uploadFolder)
    {
        if (empty($_FILES)) {
            throw new RuntimeException('No file to upload');
        }

        $tempFile = $_FILES['file']['tmp_name'];
        $uploadedFile = md5($tempFile.uniqid());
        move_uploaded_file($tempFile, $uploadFolder.$uploadedFile);
        return array('success' => true, 'uploadFilename' => $uploadedFile, 'originalFilename' => $_FILES['file']['name'] );
    }

    /**
     * @param  string $response
     * @param  boolean $error
     * @return void
     */
    private function sendResponse($response, $error)
    {
        header('HTTP/1.1 '.($error ? '400' : '200 OK'));
        header('Content-type: application/json');
        echo json_encode($response);
        exit();
    }
}
