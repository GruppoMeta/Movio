<?php
interface org_glizy_middleware_IMiddleware
{
    public function beforeProcess($pageId, $pageType);
    public function afterRender($content);
}
