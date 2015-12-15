<?php
class org_glizycms_roleManager_controllers_Login extends org_glizy_mvc_core_Command
{
    function execute()
    {
        if ($this->user->isLogged()) {
            $siteMap = $this->application->getSiteMap();
            $siteMapIterator = org_glizy_ObjectFactory::createObject('org.glizy.application.SiteMapIterator',$siteMap);
            while (!$siteMapIterator->EOF)
            {
                $n = $siteMapIterator->getNode();
                $siteMapIterator->moveNext();
                if ($n->isVisible && $n->depth > 1 && !$n->select) {
                    org_glizy_helpers_Navigation::gotoUrl( $n->id );
                }
            }

            $authClass = org_glizy_ObjectFactory::createObject(__Config::get('glizy.authentication'));
            $authClass->logout();
        }
    }
}
