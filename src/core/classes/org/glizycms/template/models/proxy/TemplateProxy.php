<?php
class org_glizycms_template_models_proxy_TemplateProxy extends GlizyObject
{
    private $application;
    private $templateName;
    private $templateValues;

    function __construct()
    {
        $this->application = org_glizy_ObjectValues::get('org.glizy', 'application');
        $templatePath = __Paths::get('APPLICATION_TO_ADMIN_TEMPLATE');
        if (!$templatePath) {
            $tempPath = __Config::get('APPLICATION_TO_ADMIN_TEMPLATE');
            __Paths::set('APPLICATION_TO_ADMIN_TEMPLATE', $tempPath ? $tempPath : __Paths::get('BASE').__Config::get('STATIC_FOLDER').'templates');
        }
    }

    public function getAvailableTemplates()
    {
        $templatePath = __Paths::get('APPLICATION_TO_ADMIN_TEMPLATE');
        $templates = array();
        if ($dh = @opendir($templatePath)) {
            // scan the template repository
            while ($dirName = readdir($dh)) {
                // check if the item is a folder
                if ($dirName!="." &&
                    $dirName!=".." &&
                    is_dir($templatePath.'/'.$dirName))
                {
                    // is a folder
                    // check if ther eis the rpeview image
                    // withou the preview the template isn't added
                    if (file_exists($templatePath.'/'.$dirName.'/preview.jpg') && !file_exists($templatePath.'/'.$dirName.'/disabled'))
                    {
                        // check and include the locale file
                        if (file_exists($templatePath.'/'.$dirName.'/locale/'.$this->application->getLanguage().'.php'))
                        {
                            include ($templatePath.'/'.$dirName.'/locale/'.$this->application->getLanguage().'.php');
                            $templateName = __T($dirName);
                        }
                        else
                        {
                            $templateName = $dirName;
                        }

                        $templates[] = org_glizy_ObjectFactory::createObject('org.glizycms.template.models.TemplateVO', $templateName, $dirName, $templatePath.'/'.$dirName.'/preview.jpg');
                    }
                }
            }
            closedir($dh);
        }
        else
        {
            // TODO
            // show error
        }

        return $templates;
    }

    public function getSelectedTemplate()
    {
        if (!$this->templateName) {
            $this->templateName = org_glizy_Registry::get(__Config::get('REGISTRY_TEMPLATE_NAME'), __Config::get('glizycms.template.default'));
        }
        return $this->templateName;
    }

    public function setSelectedTemplate($name)
    {
        $this->templateName = $name;
        org_glizy_Registry::set(__Config::get('REGISTRY_TEMPLATE_NAME'), $name);
    }

    public function getTemplateRealpath()
    {
        if ($this->application->isAdmin()) {
            return org_glizy_Paths::getRealPath('APPLICATION_TO_ADMIN_TEMPLATE', $this->getSelectedTemplate());
        } else {
            return org_glizy_Paths::getRealPath('APPLICATION_TEMPLATE');
        }
    }

    public function getTemplateCustomClass()
    {
        $templatePath = $this->getTemplateRealpath();
        if (file_exists($templatePath.'/Template.php'))
        {
            require_once($templatePath.'/Template.php');
            return new Template();
        }
        return null;
    }

    public function getTemplateAdmin()
    {
        $templatePath = $this->getTemplateRealpath();
        return file_exists($templatePath.'/TemplateAdmin.xml') ? $templatePath.'/TemplateAdmin.xml' : false;
    }

    public function getTemplateAdminGlobal()
    {
        $templatePath = $this->getTemplateRealpath();
        return file_exists($templatePath.'/TemplateAdminGlobal.xml') ? $templatePath.'/TemplateAdminGlobal.xml' : false;
    }

    public function loadTemplateLocale()
    {
        $language = $this->application->getLanguage();
        $templatePath = $this->getTemplateRealpath();
        if (file_exists($templatePath.'/locale/'.$language.'.php'))
        {
            include ($templatePath.'/locale/'.$language.'.php');
        }
    }

    public function getEditDataForMenu($menuId, $readFromParent)
    {
        $this->getData();
        if ($readFromParent && $menuId > 0){
            $data = $this->getDataForMenu($menuId, true);
        } else {
            $data = property_exists($this->templateValues, $menuId) ? $this->templateValues->{$menuId} : new StdClass;
        }
        $data->__id = $menuId;
        return $data;
    }

    public function getDataForMenu($menuId, $readFromParent=false)
    {
        $this->getData();

        if (!$readFromParent && property_exists($this->templateValues, $menuId)) {
            $data = $this->templateValues->{$menuId};
            $data->__id = $menuId;
        } else {
            $data = new StdClass;
            $menuProxy = org_glizy_ObjectFactory::createObject('org.glizycms.contents.models.proxy.MenuProxy');
            $siteMap = $menuProxy->getSiteMap();
            $menu = $siteMap->getNodeById($menuId);

            // scorre tutti i parent per trovare il primo menù che ha i dati
            while ($menu->parentId > 0) {
                // $tempMenuId = $menu->parentId;
                $menu = $siteMap->getNodeById($menu->parentId);
                if (property_exists($this->templateValues, $menu->id)) {
                    $data = $this->templateValues->{$menu->id};
                    $data->__id = $menu->id;
                    break;
                }
            }
        }

        // merge the data with global data
        if ($menuId != 0) {
            $globalData = $this->getDataForMenu(0);
            foreach($globalData as $k=>$v) {
                if (!property_exists($data, $k)) {
                    $data->{$k} = $v;
                }
            }
        }

        return $data;
    }


    public function saveEditData($data)
    {
        $data = json_decode($data);
        $menuId = $data->__id;
        if (!$menuId) $menuId = 0;
        unset($data->__id);
        unset($data->templateEdit_command);
        $this->getData();
        $this->templateValues->{$menuId} = $data;
        $this->setData();
        $this->invalidateCache();
    }

    public function getTemplateCache()
    {
        $cache = org_glizy_ObjectFactory::createObject('org.glizy.cache.CacheFunction',
                                                $this,
                                                -1,
                                                false,
                                                __Paths::getRealPath('APPLICATION_TO_ADMIN_CACHE'));
        return $cache;
    }

    public function invalidateCache()
    {
        $cache = $this->getTemplateCache();
        $cache->invalidateGroup();
    }

    private function getData()
    {
        if (!$this->templateValues) {
            $this->templateValues = json_decode(org_glizy_Registry::get(__Config::get('REGISTRY_TEMPLATE_VALUES').$this->getSelectedTemplate(), '{}'));
        }
    }

    private function setData()
    {
        org_glizy_Registry::set(__Config::get('REGISTRY_TEMPLATE_VALUES').$this->getSelectedTemplate(), json_encode($this->templateValues));
    }
}
