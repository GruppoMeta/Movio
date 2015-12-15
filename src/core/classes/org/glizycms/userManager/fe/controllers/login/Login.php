<?php
class org_glizycms_userManager_fe_controllers_login_Login extends org_glizy_mvc_core_Command
{
    public function execute()
    {
        $c = $this->view->getComponentById('formLoginPage');
        if (is_object($c)) {
            $url = $this->view->loadContent('loginPage');
            $speakingUrlManager = $this->application->retrieveProxy('org.glizycms.speakingUrl.Manager');
            $c->setAttribute('accessPageId', $speakingUrlManager->makeUrl($url));
        }
    }
}