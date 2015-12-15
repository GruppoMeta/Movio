<?php
class movio_modules_news_UrlResolver extends org_glizycms_speakingUrl_AbstractDocumentResolver implements org_glizycms_speakingUrl_IUrlResolver
{
    public function __construct()
    {
        parent::__construct();
        $this->type = 'movio.modules.news';
        $this->protocol = 'movioNews:';
        $this->model = 'movio.modules.news.models.Model';
        $this->pageType = 'movio.modules.news.views.FrontEnd';
        $this->modelName = __T('movio.modules.news.views.FrontEnd');
    }

    public function compileRouting($ar)
    {
        return '';
    }
}
