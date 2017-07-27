<?php
class org_glizycms_speakingUrl_AbstractUrlResolver
{
    protected $application;
    protected $languageId;
    protected $editLanguageId;
    protected $type;
    protected $protocol;

    public function __construct()
    {
        $this->application = org_glizy_ObjectValues::get('org.glizy', 'application');
        $this->languageId = $this->application->getLanguageId();
        $this->editLanguageId = org_glizy_ObjectValues::get('org.glizy', 'editingLanguageId');
    }


    public function getType()
    {
        return $this->type;
    }

    public function checkProtocol($id)
    {
        $info = $this->extractProtocolAndId($id);
        return $this->protocol == $info->protocol;
    }

    protected function getIdFromLink($id)
    {
        return str_replace($this->protocol, '', $id);
    }

    protected function extractProtocolAndId($id)
    {
        list($protocol, $id) = explode(':', $id);
        $result = new StdClass;
        $result->protocol = $protocol;
        $result->id = $id;
        return $result;
    }

}
