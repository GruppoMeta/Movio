<?php
class org_glizy_middleware_DynamicPage extends org_glizy_middleware_AbstractHttpCache
{
    public function beforeProcess($pageId, $pageType)
    {
    }

    public function afterRender($content)
    {
        $this->etag = md5($content.var_export(__Request::getAllAsArray(), true));
        $this->checkIfIsChanged();
    }
}