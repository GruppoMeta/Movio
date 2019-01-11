<?php
abstract class movio_views_AbstractLessTemplate extends GlizyObject
{
    protected $path;
    protected $templateName;

    abstract protected function applyCssVariables(&$templateData, $less, $css);
    abstract protected function applyFont(&$templateData, $css);
    abstract protected function addLogoCss(&$templateData, $css);
    abstract protected function addCustomCss(&$templateData, $css);
    abstract public function fixTemplateData(&$templateData, &$newTemplateData);
    abstract protected function fixTemplateName($view);

    public function render($application, $view, $templateData)
    {
        $templateData = $this->getTemplateDataFromCache($templateData);

        $siteProp = unserialize(org_glizy_Registry::get(__Config::get('REGISTRY_SITE_PROP').$view->_application->getLanguage(), ''));
        $view->addOutputCode($templateData->css, 'css');
        $view->addOutputCode($siteProp['title'], 'siteTitle');
        $view->addOutputCode($siteProp['subtitle'], 'siteSubtitle');
        if ($templateData->footerLogo) {
            $view->addOutputCode($templateData->footerLogo, 'logoFooter');
        }
        $this->fixTemplateName($view);
    }

    protected function getTemplateDataFromCache($templateData)
    {
        $templateProxy = org_glizy_ObjectFactory::createObject('org.glizycms.template.models.proxy.TemplateProxy');
        // $templateProxy->invalidateCache();
        $cache = $templateProxy->getTemplateCache();

        $cssFileName = __Paths::get('CACHE').md5($this->path.'_'.$templateData->__id.__Config::get('APP_VERSION')).'.css';
        $self = $this;
        $templateData = $cache->get($cssFileName, array(), function() use ($self, $templateData, $cssFileName) {
            $newTemplateData = new StdClass;
            $newTemplateData->footerLogo = '';

            $self->compileCss($templateData, $cssFileName);
            $self->fixTemplateData($templateData, $newTemplateData);

            $newTemplateData->css = $templateData->css;
            return $newTemplateData;
        });

        return $templateData;
    }

    public function compileCss(&$templateData, $cssFileName)
    {
        glz_importLib('lessphp/lessc.inc.php');
        $less = new lessc;
        $less->setImportDir(array($this->path.'/less/'));
        $css = file_get_contents($this->path.'/less/styles.less');
        // $css = $this->applyCssVariables($templateData, $less, $css);
        $css = $this->applyFont($templateData, $css);
        $css = $less->compile($css);
        $css = $this->fixUrl($css);
        $css = $this->addLogoCss($templateData, $css);
        $css = $this->addCustomCss($templateData, $css);
        file_put_contents($cssFileName, $css);
        $templateData->css = '<link rel="stylesheet" href="'.$cssFileName.'" type="text/css" media="screen" />';
    }

    protected function fixUrl($css)
    {
        $css = str_replace(
                array('../img/', '../font/'),
                array('../static/movio/templates/'.$this->templateName.'/img/', '../static/movio/templates/'.$this->templateName.'/font/'),
                $css);
        return $css;
    }
}
