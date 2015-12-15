<?php
class movio_modules_touristoperators_UrlResolver extends org_glizycms_speakingUrl_AbstractDocumentResolver implements org_glizycms_speakingUrl_IUrlResolver
{
    public function __construct()
    {
        parent::__construct();
        $this->type = 'movio.modules.touristoperators';
        $this->protocol = 'movioTouristOperators:';
        $this->model = 'movio.modules.touristoperators.models.Model';
        $this->pageType = 'movio.modules.touristoperators.views.FrontEnd';
        $this->modelName = __T('movio.modules.touristoperators.views.FrontEnd');
    }

    public function compileRouting($ar)
    {
        return '';
    }
}
