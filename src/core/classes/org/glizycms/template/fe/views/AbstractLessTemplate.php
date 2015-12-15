<?php
abstract class org_glizycms_template_fe_views_AbstractLessTemplate extends GlizyObject
{
    protected $path;

    abstract protected function applyCssVariables(&$templateData, $less, $css);
    abstract protected function applyFont(&$templateData, $css);
    abstract protected function addLogoCss(&$templateData, $css);
    abstract protected function addCustomCss(&$templateData, $css);
    abstract protected function addCustomOutput(&$view, &$templateData);
    abstract public function fixTemplateData(&$templateData);
    abstract protected function fixTemplateName($view);

    public function render($application, $view, $templateData)
    {
        $templateData = $this->getTemplateDataFromCache($templateData);

        $siteProp = unserialize(org_glizy_Registry::get(__Config::get('REGISTRY_SITE_PROP').$view->_application->getLanguage(), ''));
        $view->addOutputCode($templateData->css, 'css');
        $view->addOutputCode($siteProp['title'], 'siteTitle');
        $view->addOutputCode($siteProp['subtitle'], 'siteSubtitle');
        $this->fixTemplateName($view);
        $this->addCustomOutput($view, $templateData);
    }

    protected function getTemplateDataFromCache($templateData)
    {
        $templateProxy = org_glizy_ObjectFactory::createObject('org.glizycms.template.models.proxy.TemplateProxy');
        // $templateProxy->invalidateCache();
        $cache = $templateProxy->getTemplateCache();

        $cssFileName = __Paths::get('CACHE').md5($this->getClassName().'_'.$templateData->__id).'.css';
        $self = $this;
        $templateData = $cache->get($cssFileName, array(), function() use ($self, $templateData, $cssFileName) {

            $self->fixTemplateData($templateData);
            $self->compileCss($templateData, $cssFileName);

            return $templateData;
        });

        return $templateData;
    }

    public function compileCss(&$templateData, $cssFileName)
    {
        glz_importLib('lessphp/lessc.inc.php');
        $less = new lessc;
        $less->setImportDir(array($this->path.'/less/'));
        $css = file_get_contents($this->path.'/less/styles.less');
        $css = $this->applyCssVariables($templateData, $less, $css);
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
        $templatePath = __Paths::get('APPLICATION_TEMPLATE');
        $css = str_replace(
                array('../img/', '../font/'),
                array('../'.$templatePath.'/img/', '../'.$templatePath.'/font/'),
                $css);
        return $css;
    }

    protected function applyCssVariablesFromJson(&$templateData, $less, $css)
    {
        $colorsData = json_decode(file_get_contents($this->path.'/colors.json'));
        if ($colorsData) {
            $variables = array();
            foreach($colorsData as $part=>$colors) {
                foreach($colors as $colorLabel=>$colorData) {
                    $firstId = $colorData->id[0];
                    if (property_exists($templateData, $firstId)) {
                        $value = $templateData->{$firstId};
                        foreach($colorData->id as $v) {
                            $css .= '@'.$v.':'.$value.';';
                        }

                    }
                }
            }
        }
        return $css;
    }
}