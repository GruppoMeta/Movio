<?php
class movio_modules_publishApp_controllers_Index extends org_glizy_mvc_core_Command
{
    public function execute()
    {
        if (method_exists($this->view, 'setData')) {
            $siteProp = unserialize(org_glizy_Registry::get(__Config::get('REGISTRY_SITE_PROP').$this->application->getLanguage(), ''));
            $lastUpdate = org_glizy_Registry::get('movio/modules/publishApp/lastUpdate');
            
            $data = new StdClass;
            $data->title = $siteProp['title'];
            $data->subtitle = $siteProp['subtitle'];
            
            if ($lastUpdate) {
                $data->lastUpdate = '<p>'.__T('Last exportation date').': '. date(__T('GLZ_DATETIME_FORMAT').'</p>', $lastUpdate);
            }
            
            $data->isExhibitionActive = 1;
            
            $it = org_glizy_ObjectFactory::createModelIterator('org.glizycms.core.models.Language')
                ->load('getLanguageDictionary');
                
            $this->setComponentsAttribute('languages', 'rows', $it->count());
        
            $this->view->setData($data);
        }
    }
}