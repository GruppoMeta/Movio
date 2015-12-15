<?php
class org_glizy_middleware_StaticPage extends org_glizy_middleware_AbstractHttpCache
{
    protected $etag;
    protected $lastModifiedTime;

    public function beforeProcess($pageId, $pageType)
    {
        $fileName = org_glizy_Paths::getRealPath('APPLICATION_PAGE_TYPE').$pageType.'.xml';
        $this->lastModifiedTime = filemtime($fileName);
        $this->etag = md5_file($fileName);

        $this->checkIfIsChanged();
    }

    public function afterRender($content)
    {
    }
}