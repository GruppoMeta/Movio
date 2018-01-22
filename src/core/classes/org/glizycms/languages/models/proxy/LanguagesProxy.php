<?php
class org_glizycms_languages_models_proxy_LanguagesProxy extends org_glizycms_contents_models_proxy_ActiveRecordProxy
{
    private static $defaultLanguageId;

    public function save($data)
    {
        $isNew = !(intval($data->__id) && $data->__id > 0);
        $isDefault = $data->language_isDefault;
        $currentDefaultId = 0;
        $countryId = $data->language_FK_country_id;
        if ($countryId) {
            $ar = org_glizy_ObjectFactory::createModel('org.glizycms.core.models.Country');
            if ($ar->load($countryId)) {
                $data->language_code = $ar->country_639_1;
            }
        } else {
            $data->language_code = '';
        }

        if (!$data->language_order) {
            $data->language_order = 1;
        }

        // if isDefault read the current defautl language
        $ar = org_glizy_ObjectFactory::createModel('org.glizycms.core.models.Language');
        if ($ar->find(array('language_isDefault' => 1))) {
            $currentDefaultId = $ar->language_id;
        }

        if (!$isDefault && $currentDefaultId==$data->__id) {
            return array(__T('You can\'t remove the default proerty, to do it set the default to other record'));
        } else if ($isDefault && $currentDefaultId!=$data->__id) {
            $ar->language_isDefault = 0;
            $ar->save();
        }

        if ($isNew && !$currentDefaultId) {
            return array(__T('Can\'t create a new language if there aren\'t a default one'));
        }

        $result = parent::save($data);

        if ($isNew && $currentDefaultId) {
            $this->duplicateMenu($currentDefaultId, $result['__id']);
            $this->duplicateMedia($currentDefaultId, $result['__id']);
        }
    }


    public function delete($recordId)
    {
        $this->deleteMenu($recordId);
        $this->deleteContents($recordId);
        $this->deleteMedia($recordId);
        parent::delete($recordId, 'org.glizycms.core.models.Language');
    }

    private function duplicateMenu($languageId, $newLanguageId)
    {
        $it = org_glizy_ObjectFactory::createModelIterator('org.glizycms.core.models.Language');
        $it->load('duplicateMenuDetail', array(':languageId' => $languageId, ':newLanguageId' => $newLanguageId));
        $it->exec();
    }

    private function duplicateContents($languageId, $newLanguageId)
    {
        $it = org_glizy_ObjectFactory::createModelIterator('org.glizycms.core.models.Language');
        $it->load('duplicateDocumentsDetail', array(':languageId' => $languageId, ':newLanguageId' => $newLanguageId));
        $it->exec();
    }

    private function duplicateMedia($languageId, $newLanguageId)
    {
        $it = org_glizy_ObjectFactory::createModelIterator('org.glizycms.core.models.Language');
        $it->load('duplicateMediaDetail', array(':languageId' => $languageId, ':newLanguageId' => $newLanguageId));
        $it->exec();
    }

    private function deleteMenu($languageId)
    {
        $it = org_glizy_ObjectFactory::createModelIterator('org.glizycms.core.models.Language');
        $it->load('deleteMenuDetail', array(':languageId' => $languageId));
        $it->exec();
    }

    private function deleteContents($languageId)
    {
        $it = org_glizy_ObjectFactory::createModelIterator('org.glizycms.core.models.Language');
        $it->load('deleteDocumentsDetail', array(':languageId' => $languageId));
        $it->exec();
    }

    private function deleteMedia($languageId)
    {
        $it = org_glizy_ObjectFactory::createModelIterator('org.glizycms.core.models.Language');
        $it->load('deleteMediaDetail', array(':languageId' => $languageId));
        $it->exec();
    }

    public function getLanguageId()
    {
        $editingLanguageId = org_glizy_ObjectValues::get('org.glizy', 'editingLanguageId');
        if (!is_null($editingLanguageId)) {
            return $editingLanguageId;
        } else {
            return org_glizy_ObjectValues::get('org.glizy', 'languageId');
        }
    }

    public function getDefaultLanguageId()
    {
        if (self::$defaultLanguageId) {
            return self::$defaultLanguageId;
        }

        $ar = org_glizy_ObjectFactory::createModel('org.glizycms.core.models.Language');

        if (!__Config::get('MULTILANGUAGE_ENABLED')) {
            $ar->resetSiteField();
        }

        $ar->find(array('language_isDefault' => 1));
        self::$defaultLanguageId = $ar->language_id;
        return self::$defaultLanguageId;
    }

    public function findLanguageByCountry($languageCountryId, $id)
    {
        $ar = org_glizy_ObjectFactory::createModel('org.glizycms.core.models.Language');
        if (!__Config::get('MULTILANGUAGE_ENABLED')) {
            $ar->resetSiteField();
        }

        $r = $ar->find(array('language_FK_country_id' => $languageCountryId));
        return $r && $ar->language_id!=$id;
    }
}