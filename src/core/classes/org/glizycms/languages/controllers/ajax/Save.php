<?php
class org_glizycms_languages_controllers_ajax_Save extends org_glizy_mvc_core_CommandAjax
{
    public function execute($data)
    {
    	$this->checkPermissionForBackend();
        $this->directOutput = true;

        $data = json_decode($data);

        $proxy = org_glizy_objectFactory::createObject('org.glizycms.languages.models.proxy.LanguagesProxy');
        if ($proxy->findLanguageByCountry($data->language_FK_country_id, @$data->__id)) {
            return array('errors' => array(__T('LANGUAGE_ALREADY_PRESENT')));
        }

        $result = $proxy->save($data);

        if ($result['__id']) {
            return array('set' => $result);
        } else {
            return array('errors' => $result);
        }
    }
}