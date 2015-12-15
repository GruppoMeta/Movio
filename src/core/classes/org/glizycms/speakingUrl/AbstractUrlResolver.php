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

    protected function getIdFromLink($id)
    {
        return str_replace($this->protocol, '', $id);
    }
}
