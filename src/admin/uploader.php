<?php
require_once("../core/core.inc.php");
$application = org_glizy_ObjectFactory::createObject('org.glizycms.core.application.AdminApplication', 'application', '../', '../application/');
$application->runSoft();
$user = $application->getCurrentUser();
if ($user->isLogged()) {
    org_glizy_helpers_Files::deleteDirectory('cache/uploads/', 2*24*60);
    processUploadFile();
} else {
    header("HTTP/1.0 403 Forbidden");
}

function processUploadFile()
{
    $uploadFolder = 'cache/uploads/';
    if (!empty($_FILES)) {
        if (!file_exists($uploadFolder)) {
            @mkdir($uploadFolder);
        }

        $tempFile = $_FILES['file']['tmp_name'];
        $uploadedFile = $uploadFolder.md5($_FILES['file']['tmp_name']);
        move_uploaded_file($tempFile, $uploadedFile);
        sendResponse(array('success'=>true, 'uploadFilename' => $uploadedFile, 'originalFilename' => $_FILES['file']['name'] ), false);
    } else {
        sendResponse('error', true);
    }
}

function sendResponse($response, $error)
{
    header('HTTP/1.1 '.($error ? '400' : '200 OK'));
    header('Content-type: application/json');
    echo json_encode($response);
    exit();

}

