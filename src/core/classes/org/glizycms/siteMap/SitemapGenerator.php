<?php

class org_glizycms_siteMap_SitemapGenerator
{
    protected $sitemapxml;

    public function generate($applicationSiteMap)
    {
        $this->sitemapxml = $this->siteMapForPages($applicationSiteMap);

        $modules = org_glizy_Modules::getModules();
        $this->sitemapxml .= $this->siteMapForModules($applicationSiteMap, org_glizy_Modules::getModules());
    }

    public function finalize()
    {
        return $this->finalXml($this->sitemapxml);
    }

    public function write($xml, $fileName='sitemap.xml')
    {
        return file_put_contents($fileName, $xml);
    }

    /**
     * @return string
     */
    private function siteMapForPages($applicationSiteMap)
    {
        $sitemapxml = '';
        $siteMapIterator = org_glizy_ObjectFactory::createObject('org.glizy.application.SiteMapIterator', $applicationSiteMap);
        while (!$siteMapIterator->EOF) {
            $n = $siteMapIterator->getNode();
            $siteMapIterator->moveNext();
            if (!$n->isVisible ||
                $n->isLocked ||
                $n->hideInNavigation ||
                $n->pageType=='Empty' ||
                $n->pageType=='Alias' ||
                $n->type!='PAGE' ) continue;

            $url = $n->url ? GLZ_HOST.'/'.$n->url : __Link::makeUrl('link', array('pageId' => $n->id));
            $sitemapxml .= $this->printNode($url);
        }

        return $sitemapxml;
    }

    /**
     * @param  org_glizy_application_SiteMap $applicationSiteMap
     * @param  array(org_glizy_ModuleVO)  $module
     * @return string
     */
    private function siteMapForModules($applicationSiteMap, $modules)
    {
        $sitemapxml = '';
        foreach( $modules as $m ) {
            if (!$m->pageType) continue;
            $menu = $applicationSiteMap->getMenuByPageType($m->pageType);
            if (!$menu->isVisible || $menu->isLocked) continue;
            $sitemapxml .= $this->siteMapForSingleModule($applicationSiteMap, $m);
        }

        return $sitemapxml;
    }

    /**
     * @param  org_glizy_application_SiteMap $applicationSiteMap
     * @param  org_glizy_ModuleVO  $module
     * @return string
     */
    private function siteMapForSingleModule($applicationSiteMap, $module)
    {
        $sitemapxml = '';
        $speakingUrlManager = __ObjectFactory::createObject('org.glizycms.speakingUrl.Manager');
        $urlResolver = $speakingUrlManager->getResolver($module->id);
        if (!$urlResolver) return $sitemapxml;

        $model = $urlResolver->modelName();
        $it = __ObjectFactory::createModelIterator($model);
        foreach($it as $ar) {
            if (!$ar->document_detail_isVisible) continue;
            $sitemapxml .= $this->printNode($urlResolver->makeUrlFromModel($ar));
        }

        return $sitemapxml;
    }

    /**
     * @param  strin $url
     * @return string
     */
    protected function printNode($url)
    {
        return sprintf('<url><loc>%s</loc><changefreq>weekly</changefreq></url>', $url);
    }

    /**
     * @param  string $sitemapxml
     * @return string
     */
    private function finalXml($sitemapxml)
    {
        $xml = <<<EOD
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
$sitemapxml
</urlset>
EOD;
        return $xml;
    }
}
