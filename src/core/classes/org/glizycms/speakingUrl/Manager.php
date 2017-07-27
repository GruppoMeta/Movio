<?php
class org_glizycms_speakingUrl_Manager extends GlizyObject
{
    private static $modules = array();

    public function __construct()
    {
        $application = org_glizy_ObjectValues::get('org.glizy', 'application');
        if (__Config::get('glizycms.speakingUrl') && !$application->isAdmin()) {
            $this->addEventListener(GLZ_EVT_START_COMPILE_ROUTING, $this);
        }
    }


    public static function registerResolver($resolver)
    {
        self::$modules[$resolver->getType()] = $resolver;
    }

    public function getResolver($type)
    {
        return isset(self::$modules[$type]) ? self::$modules[$type] : null;
    }


    public function startCompileRouting()
    {
        $this->compileRouting();
    }


    private function compileRouting()
    {
        $routing = '';

        $it = org_glizy_ObjectFactory::createModelIterator('org.glizycms.speakingUrl.models.SpeakingUrl')
             ->load('all');
        foreach($it as $ar) {
            if (isset(self::$modules[$ar->speakingurl_type])) {
                $routing .= self::$modules[$ar->speakingurl_type]->compileRouting($ar);
            }
        }

        $routing = '<?xml version="1.0" encoding="utf-8"?><glz:Routing>'.$routing.'</glz:Routing>';
        $evt = array('type' => GLZ_EVT_LISTENER_COMPILE_ROUTING, 'data' => $routing);
        $this->dispatchEvent($evt);
    }

    public function searchDocumentsByTerm($term, $id, $protocol='', $filterType='')
    {
        $result = array();

        foreach (self::$modules as $module) {
            $partialResult = $module->searchDocumentsByTerm($term, $id, $protocol, $filterType);
            $result = array_merge($result, $partialResult);
        }

        return $result;
    }

    // TODO sostituire i due metodo con resolve
    public function makeUrl($id)
    {
        foreach (self::$modules as $module) {
            $url = $module->makeUrl($id);

            if ($url!==false) {
                return $url;
            }
        }

        return false;
    }

    public function makeLink($id)
    {
        foreach (self::$modules as $module) {
            $url = $module->makeLink($id);

            if ($url!==false) {
                return $url;
            }
        }

        return false;
    }

    public function resolve($id)
    {
        foreach (self::$modules as $module) {
            if (method_exists($module, 'resolve')) {
                $resolveVO = $module->resolve($id);
                if ($resolveVO!==false) {
                    return $resolveVO;
                }
            }
        }

        return false;
    }

    public function onRegister()
    {
    }
}
