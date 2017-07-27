<?php
abstract class org_glizycms_template_fe_views_AbstractTemplate extends GlizyObject
{
    protected $path;

    abstract protected function fixTemplateName($view);

    public function render($application, $view, $templateData)
    {
        $siteProp = unserialize(org_glizy_Registry::get(__Config::get('REGISTRY_SITE_PROP').$view->_application->getLanguage(), ''));
        $view->addOutputCode($siteProp['title'], 'siteTitle');
        $view->addOutputCode($siteProp['subtitle'], 'siteSubtitle');
        $this->fixTemplateName($view);
    }
}