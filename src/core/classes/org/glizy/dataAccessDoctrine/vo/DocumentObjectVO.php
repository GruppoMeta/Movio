<?php

class org_glizy_dataAccessDoctrine_vo_DocumentObjectVO extends GlizyObject
{
    const DOCUMENT_DETAIL_OBJECT = 'document_detail_object';
    const DOCUMENT_DETAIL_STATUS = 'document_detail_status';

    protected $data;
    protected $hasPublishedVersion;
    protected $hasDraftVersion;

    function __construct($data)
    {
        $languageProxy = __ObjectFactory::createObject('org.glizycms.languages.models.proxy.LanguagesProxy');

        $index = null;

        if (is_array($data[self::DOCUMENT_DETAIL_STATUS])) {
            $index = array_search('PUBLISHED', $data[self::DOCUMENT_DETAIL_STATUS]);
            $this->hasPublishedVersion = $index !== FALSE;

            $indexDraft = array_search('DRAFT', $data[self::DOCUMENT_DETAIL_STATUS]);
            $this->hasDraftVersion = $indexDraft !== FALSE;

            if (!$this->hasPublishedVersion) {
                $index = $indexDraft;
            }
        }

        if ($index && $data['document_detail_FK_language_id'][$index] != $languageProxy->getLanguageId()) {
           $data['document_detail_FK_language_id'][$index] = $languageProxy->getLanguageId();
           $data['document_detail_translated'][$index] = 0;
        }

        foreach ($data as $k => $v) {
            if (is_array($v)) {
                $this->data[$k] = $v[$index];
            } else {
                $this->data[$k] = $v;
            }
        }
    }

    public function __get($name)
    {
        return $this->data[$name];
    }

    public function hasPublishedVersion()
    {
       return $this->hasPublishedVersion;
    }

    public function hasDraftVersion()
    {
       return $this->hasDraftVersion;
    }
}