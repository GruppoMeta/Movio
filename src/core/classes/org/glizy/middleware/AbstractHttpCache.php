<?php
abstract class org_glizy_middleware_AbstractHttpCache implements org_glizy_middleware_IMiddleware
{
    protected $etag;
    protected $lastModifiedTime;

    protected function checkIfIsChanged() {
        $this->setEtag();

        $ifModifiedSince = (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? @strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) : false);
        $etagHeader = (isset($_SERVER['HTTP_IF_NONE_MATCH']) ? trim($_SERVER['HTTP_IF_NONE_MATCH']) : false);

        if (($this->lastModifiedTime && $ifModifiedSince == $this->lastModifiedTime) || ($etagHeader == $this->etag)) {
            header('HTTP/1.1 304 Not Modified');
            exit;
        }
    }

    protected function setEtag()
    {
        header("Cache-Control: private");
        header("Pragma:");
        header("Expires:");
        if ($this->lastModifiedTime) header('Last-Modified: '.gmdate('D, d M Y H:i:s', $this->lastModifiedTime).' GMT');
        header('Etag: '.$this->etag);
    }
}
